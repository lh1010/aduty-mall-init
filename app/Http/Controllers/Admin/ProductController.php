<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;
use App\Repositorys\Admin\ProductRepository;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductController extends BaseController
{
    public function list(Request $request)
    {
        $ProductRepository = new ProductRepository;
        $params = $request->all();
        $query = DB::table('product');
        $query->select(['product.*']);

        if (isset($params['category_id']) && !empty($params['category_id'])) {
            $category_child_ids = $ProductRepository->getCategoryChildIds($params['category_id']);
            $query->whereIn('product.category_id', $category_child_ids);
        }
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('product.name', 'like', "%" . $params['k'] . "%");
        }
        $query->where('product.status', '<>', 99);

        $query->orderBy('product.id', 'desc');
        $products = $query->paginate();
        $ids = array_column($products->items(), 'id');

        $product_skus = DB::table('product_sku')->whereIn('product_id', $ids)->get()->toArray();
        $skus = array_column($product_skus, 'sku');
        $product_to_specifications = DB::table('product_to_specification')->whereIn('sku', $skus)->get()->toArray();
        $array = [];
        foreach ($product_to_specifications as $key => $value) {
            $array[$value->sku][] = $value;
        }
        foreach ($product_skus as $key => $value) {
            $product_skus[$key]->specifications = isset($array[$value->sku]) ? $array[$value->sku] : [];
        }
        $array = [];
        foreach ($product_skus as $key => $value) {
            $array[$value->product_id][] = $value;
        }
        foreach ($products as $key => $value) {
            $products[$key]->skus = isset($array[$value->id]) ? $array[$value->id] : [];
            $product_sku = !empty($array[$value->id]) ? $array[$value->id][0] : [];
            $products[$key]->sku = !empty($product_sku) ? $product_sku->sku : '';
            $products[$key]->price = !empty($product_sku) ? $product_sku->price : 0;
            $products[$key]->stock = !empty($product_sku) ? $product_sku->stock : 0;
        }

        foreach ($products as $key => $value) {
            $products[$key]->cover = !empty($value->cover) ? $value->cover : Config('common.image.product_cover');
            // 分类名
            $category_parent_ids = $ProductRepository->getCategoryParentIds($value->category_id);
            $categorys_temp = Db::table('product_category')->whereIn('id', $category_parent_ids)->pluck('name')->toArray();
            $full_category_name = implode(' > ', $categorys_temp);
            $products[$key]->full_category_name = $full_category_name ? $full_category_name : '暂无分类';
        }

        $categorys = $ProductRepository->getCategorys();
        return view('admin.product.list', compact('products', 'categorys'));
    }

    public function selectCategory()
    {
        $params = ['parent_id' => 0, 'status' => 1];
        $categorys = app(ProductRepository::class)->getCategorys($params, $type = 'select');
    	return view('admin.product.select_category', compact('categorys'));
    }

    public function getCategorys(Request $request)
    {
        $params = ['parent_id' => $request->parent_id, 'status' => 1];
        $categorys = app(ProductRepository::class)->getCategorys($params, $type = 'select');
        return jsonSuccess($categorys);
    }

    public function create(Request $request)
    {
        // 分类
        $params = ['parent_id' => 0, 'status' => 1];
        $categorys = app(ProductRepository::class)->getCategorys($params, $type = 'select');

        // 商品规格组合
        $specification_groups = DB::table('product_specification_group')
                ->where('product_specification_group.status', '<>', 99)
                ->orderBy('product_specification_group.sort', 'desc')
                ->get()->toArray();

        // 商品属性组合
        $attribute_groups = DB::table('product_attribute_group')
                ->where('product_attribute_group.status', '<>', 99)
                ->orderBy('product_attribute_group.sort', 'desc')
                ->get()->toArray();

        return view('admin.product.create', compact('categorys', 'specification_groups', 'attribute_groups'));
    }

    public function store(Request $request)
    {
        $rules = [
            'category_id' => ['required'],
            'name' => ['required'],
            'specification_type' => [Rule::in(['单规格', '多规格'])],
        ];
        $messages = [
            'category_id.required' => '请选择商品分类',
            'name.required' => '商品名字不能为空',
            'specification_type.in' => '请选择正确的商品规格',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return jsonFailed($validator->errors()->first());
        $specification_type = $request->specification_type;

        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = app(ProductRepository::class)->setCreateUpdateParams($params);
            $data['spu'] = getSpu();
            $product_id = DB::table('product')->insertGetId($data);

            // images
            if (isset($params['images']) && !empty($params['images'])) {
                $data_image = [];
                foreach ($params['images'] as $key_image => $value_image) {
                    $data_image[$key_image]['product_id'] = $product_id;
                    $data_image[$key_image]['spu'] = $data['spu'];
                    $data_image[$key_image]['image'] = fileFormat($value_image);
                }
                DB::table('product_image')->insert($data_image);
            }

            // sku
            $data_sku = [];
            if ($specification_type == '单规格') {
                $sku = getSku();
                $data_sku = [
                    'product_id' => $product_id,
                    'spu' => $data['spu'],
                    'sku' => $sku,
                    'price' => $data['price'],
                    'stock' => is_numeric($data['stock']) ? $data['stock'] : 0,
                    'sort' => isset($data['sort']) && is_numeric($data['sort']) ? $data['sort'] : 0,
                    'cover' => fileFormat($params['cover'] ?? ''),
                ];
                DB::table('product_sku')->insert($data_sku);
            }

            if ($specification_type == '多规格') {
                if (!isset($params['skus']) || empty(isset($params['skus']))) {
                    DB::rollBack();
                    return jsonFailed('请选择规格');
                }
                foreach ($params['skus'] as $key => $value) {
                    $preg = "/^[1-9]+\d*(.\d{1,2})?$|^\d+.\d{1,2}$/";
                    if (!preg_match($preg, $value['price'])) {
                        DB::rollBack();
                        return jsonFailed('请正确填写规格组合中的销售价');
                    }
                    $sku = getSku();
                    $data_sku = [
                        'product_id' => $product_id,
                        'spu' => $data['spu'],
                        'sku' => $sku,
                        'price' => $value['price'],
                        'stock' => is_numeric($value['stock']) ? $value['stock'] : 0,
                        'sort' => is_numeric($value['sort']) ? $value['sort'] : 0,
                        'cover' => fileFormat($value['cover'] ?? ''),
                    ];
                    DB::table('product_sku')->insert($data_sku);

                    // 商品规格
                    $data_specification = [];
                    $specification_option_ids = explode('_', $key);
                    $specification_options = DB::table('product_specification_option')->whereIn('id', $specification_option_ids)->get()->toArray();
                    $array_specification_options = [];
                    foreach ($specification_options as $k => $v) {
                        $array_specification_options[$v->id] = $v;
                    }
                    $specification_ids = array_column($specification_options, 'specification_id');
                    $specification_ids = array_unique($specification_ids);
                    $specifications = DB::table('product_specification')->whereIn('id', $specification_ids)->get()->toArray();
                    $array_specifications = [];
                    foreach ($specifications as $k => $v) {
                        $array_specifications[$v->id] = $v;
                    }
                    foreach ($specification_option_ids as $k => $v) {
                        $data_specification[$k]['product_id'] = $product_id;
						$data_specification[$k]['spu'] = $data['spu'];
						$data_specification[$k]['sku'] = $data_sku['sku'];
						$data_specification[$k]['specification_id'] = isset($array_specification_options[$v]) ? $array_specifications[$array_specification_options[$v]->specification_id]->id : '';
                        $data_specification[$k]['specification_name'] = isset($array_specification_options[$v]) ? $array_specifications[$array_specification_options[$v]->specification_id]->name : '';
						$data_specification[$k]['specification_option_id'] = $v;
                        $data_specification[$k]['specification_option'] = isset($array_specification_options[$v]) ? $array_specification_options[$v]->option : '';
					}
                    DB::table('product_to_specification')->insert($data_specification);
                }
            }

            // 商品属性
            if (isset($params['attributes'])) {
                $attribute_ids = array_keys($params['attributes']);
                $attributes = DB::table('product_attribute')->whereIn('id', $attribute_ids)->get()->toArray();
                $array_attributes = [];
                foreach ($attributes as $key => $value) {
                    $array_attributes[$value->id] = $value;
                }
                $data_attribute = [];
                foreach ($params['attributes'] as $key => $value) {
                    $current_attribute = isset($array_attributes[$key]) ? $array_attributes[$key] : [];
                    if (!empty($current_attribute)) {
                        if ($current_attribute->required == '是' && empty($value)) {
                            DB::rollBack();
                            return jsonFailed('商品属性[' . $current_attribute->name . ']不能为空');
                        }
                    }
                    if (!empty($value)) {
						$data_attribute[$key]['product_id'] = $product_id;
						$data_attribute[$key]['spu'] = $data['spu'];
                        $data_attribute[$key]['attribute_id'] = $key;
                        $data_attribute[$key]['attribute_name'] = isset($array_attributes[$key]) ? $array_attributes[$key]->name : '';
						$data_attribute[$key]['attribute_value'] = $value;
					}
                }
                DB::table('product_to_attribute')->insert($data_attribute);
            }

            // 填充审核记录
            if ($params['status'] != 0) {
                DB::table('product_audit_log')->insert(['product_id' => $product_id, 'status' => $request->status]);
            }

            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function edit(Request $request)
    {
        $product = DB::table('product')->where('id', $request->id)->first();
        if (empty($product)) abort(404);
        $ProductRepository = new ProductRepository;

        // images
        $images = DB::table('product_image')->where('product_id', $product->id)->get()->toArray();
        foreach ($images as $key => $value) {
            $images[$key]->image = fileView($value->image);
        }
        $product->images = $images;

        // 当前类目
        $category_parent_ids = $ProductRepository->getCategoryParentIds($product->category_id);
        $categorys_temp = DB::table('product_category')->whereIn('id', $category_parent_ids)->pluck('name')->toArray();
        $selected_category = implode(' > ', $categorys_temp);

        // skus
        $skus = DB::table('product_sku')->where('spu', $product->spu)->get()->toArray();
        $sku_array = array_column($skus, 'sku');
        $product_to_specifications = DB::table('product_to_specification')->whereIn('sku', $sku_array)->get()->toArray();
        $array = [];
        foreach ($product_to_specifications as $key => $value) {
            $array[$value->sku][] = $value;
        }
        foreach ($skus as $key => $value) {
            $value->cover = fileView($value->cover);
            $skus[$key]->specifications = $array[$value->sku] ?? [];
        }
        foreach ($skus as $key => $value) {
			$skus[$key]->option_id_connect = '';
            $skus[$key]->option_connect = '';
			if (!empty($value->specifications)) {
				$option_id_connect = array_column($value->specifications, 'specification_option_id');
				$skus[$key]->option_id_connect = implode('_', $option_id_connect);
				$option_connect = array_column($value->specifications, 'specification_option');
				$skus[$key]->option_connect = implode('/', $option_connect);
			}
		}
        array_multisort($skus);
        $product->skus = $skus;

        // 商品规格组合
        $specification_groups = DB::table('product_specification_group')
                ->where('product_specification_group.status', '<>', 99)
                ->orderBy('product_specification_group.sort', 'desc')
                ->get()->toArray();

        $specifications = [];
        if ($product->specification_type == '多规格') {
            $group_id = $product->specification_group_id;
            $specifications = DB::table('product_specification')->where('group_id', $group_id)->where('status', 1)->get()->toArray();
            $specification_ids = array_column($specifications, 'id');
            $options = DB::table('product_specification_option')->whereIn('specification_id', $specification_ids)->get()->toArray();
            $array = [];
            foreach ($options as $key => $value) {
                $array[$value->specification_id][] = $value;
            }
            foreach ($specifications as $key => $value) {
                $specifications[$key]->options = isset($array[$value->id]) ? $array[$value->id] : [];
            }
        }

        // 商品属性组合
        $attribute_groups = DB::table('product_attribute_group')
                ->where('product_attribute_group.status', '<>', 99)
                ->orderBy('product_attribute_group.sort', 'desc')
                ->get()->toArray();

        $product_to_attributes = DB::table('product_to_attribute')->where('product_id', $product->id)->get()->toArray();
        $product->attributes = $product_to_attributes;
        $temp_array = [];
        foreach ($product_to_attributes as $key => $value) {
            $temp_array[$value->attribute_id] = $value;
        }
        $attributes = [];
        if ($product->attribute_group_id) {
            $group_id = $product->attribute_group_id;
            $attributes = DB::table('product_attribute')->where('group_id', $group_id)->where('status', 1)->get()->toArray();
        }
        $attribute_ids = array_column($attributes, 'id');
        $options = DB::table('product_attribute_option')->whereIn('attribute_id', $attribute_ids)->get()->toArray();
        $array = [];
        foreach ($options as $key => $value) {
            $array[$value->attribute_id][] = $value;
        }
        foreach ($attributes as $key => $value) {
            $avalue = '';
            if (isset($temp_array[$value->id])) {
                $avalue = $temp_array[$value->id]->attribute_value;
            }
            $attributes[$key]->value = $avalue;
            $attributes[$key]->options = isset($array[$value->id]) ? $array[$value->id] : [];
        }

        // 分类
        $params = ['parent_id' => 0, 'status' => 1];
        $categorys = app(ProductRepository::class)->getCategorys($params, $type = 'select');

        return view('admin.product.edit', compact('product', 'categorys', 'selected_category', 'specification_groups', 'specifications', 'attribute_groups', 'attributes'));
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => ['required'],
            'specification_type' => [Rule::in(['单规格', '多规格'])],
        ];
        $messages = [
            'name.required' => '商品名字不能为空',
            'specification_type.in' => '请选择正确的商品规格',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return jsonFailed($validator->errors()->first());
        $specification_type = $request->specification_type;

        $product = DB::table('product')->where('id', $request->id)->where('status', '<>', 99)->first();
        if (empty($product)) return jsonFailed('内容不存在');

        DB::beginTransaction();
        try {
            $params = $request->all();
            $params['category_id'] = (isset($params['category_id']) && !empty($params['category_id'])) ? $params['category_id'] : $product->category_id;
            $data = app(ProductRepository::class)->setCreateUpdateParams($params);
            DB::table('product')->where('id', $request->id)->update($data);
            $data['spu'] = $product->spu;
            $product_id = $request->id;

            // images
            if (isset($params['images']) && !empty($params['images'])) {
                $images = $params['images'];
                $image_ids = $params['image_ids'] ?? [];
                $update_ids = [];
                $data_images = [];
                foreach ($images as $key => $value) {
                    $value = fileFormat($value);
                    // 新数据
                    if (!isset($image_ids[$key]) || empty($image_ids[$key])) {
                        if (!empty($value)) {
                            $data_images[$key]['product_id'] = $product->id;
                            $data_images[$key]['spu'] = $product->spu;
                            $data_images[$key]['image'] = fileFormat($value);
                        }
                    } else {
                        // 更新旧数据
                        $update_ids[$key] = $image_ids[$key];
                        DB::table('product_image')->where('id', $image_ids[$key])->update(['image' => fileFormat($value)]);
                    }
                }
                // 删除旧数据
                DB::table('product_image')->where('product_id', $product->id)->whereNotIn('id', $update_ids)->delete();
                // 新增新数据
                DB::table('product_image')->insert($data_images);
            } else {
                DB::table('product_image')->where('product_id', $product->id)->delete();
            }

            // sku
            if ($specification_type == '单规格') {
                //DB::table('product_sku')->where('product_id', $product_id)->delete();
                $data_sku = [];
                $data_sku = [
                    'price' => $data['price'],
                    'stock' => is_numeric($data['stock']) ? $data['stock'] : 0,
                    'sort' => isset($data['sort']) && is_numeric($data['sort']) ? $data['sort'] : 0,
                    'cover' => fileFormat($params['cover'] ?? ''),
                ];
                DB::table('product_sku')->where('product_id', $product_id)->update($data_sku);
            }

            if ($specification_type == '多规格') {
                // DB::table('product_sku')->where('product_id', $product_id)->delete();
                DB::table('product_to_specification')->where('product_id', $product_id)->delete();
                if (!isset($params['skus']) || empty(isset($params['skus']))) {
                    DB::rollBack();
                    return jsonFailed('请选择规格');
                }

                $insertList_sku = [];
                $insertList_specification = [];
                $updateSkuIds = [];
                foreach ($params['skus'] as $key => $value) {
                    $preg = "/^[1-9]+\d*(.\d{1,2})?$|^\d+.\d{1,2}$/";
                    if (!preg_match($preg, $value['price'])) {
                        DB::rollBack();
                        return jsonFailed('请正确填写规格组合中的销售价');
                    }
                    $sku = getSku();
                    $data_sku = [
                        'product_id' => $product_id,
                        'spu' => $data['spu'],
                        'price' => $value['price'],
                        'stock' => is_numeric($value['stock']) ? $value['stock'] : 0,
                        'sort' => is_numeric($value['sort']) ? $value['sort'] : 0,
                        'cover' => fileFormat($value['cover'] ?? ''),
                    ];
                    // 更新旧数据
                    if (isset($value['id'])) {
                        $updateSkuIds[$key] = (int)$value['id'];
                        DB::table('product_sku')->where('id', $value['id'])->update($data_sku);
                        $sku = $value['sku'];
                    } else {
                        // 新数据
                        $data_sku['sku'] = $sku;
                        $insertList_sku[] = $data_sku;
                    }

                    // 商品规格
                    $specificationOptionIds = explode('_', $key);
                    $specificationOptions = DB::table('product_specification_option')
                        ->whereIn('id', $specificationOptionIds)
                        ->get()
                        ->keyBy('id');
                    $specificationIds = $specificationOptions->pluck('specification_id')->unique()->toArray();
                    $specifications = DB::table('product_specification')
                        ->whereIn('id', $specificationIds)
                        ->get()
                        ->keyBy('id');
                    foreach ($specificationOptionIds as $optionId) {
                        if ($specificationOptions->has($optionId)) {
                            $option = $specificationOptions[$optionId];
                            $specification = $specifications[$option->specification_id] ?? null;
                            if ($specification) {
                                $insertList_specification[] = [
                                    'product_id' => $product_id,
                                    'spu' => $data['spu'],
                                    'sku' => $sku, // 每个规格关联对应的 SKU
                                    'specification_id' => $specification->id,
                                    'specification_name' => $specification->name,
                                    'specification_option_id' => $option->id,
                                    'specification_option' => $option->option,
                                ];
                            }
                        }
                    }
                }
                DB::table('product_sku')->where('product_id', $product_id)->whereNotIn('id', $updateSkuIds)->delete();
                if (!empty($insertList_sku)) {
                    DB::table('product_sku')->insert($insertList_sku);
                }
                if (!empty($insertList_specification)) {
                    DB::table('product_to_specification')->insert($insertList_specification);
                }
            }

            // 商品属性
            DB::table('product_to_attribute')->where('product_id', $product_id)->delete();
            if (isset($params['attributes'])) {
                $attribute_ids = array_keys($params['attributes']);
                $attributes = DB::table('product_attribute')->whereIn('id', $attribute_ids)->get()->toArray();
                $array_attributes = [];
                foreach ($attributes as $key => $value) {
                    $array_attributes[$value->id] = $value;
                }
                $data_attribute = [];
                foreach ($params['attributes'] as $key => $value) {
                    if (!empty($value)) {
						$data_attribute[$key]['product_id'] = $product_id;
						$data_attribute[$key]['spu'] = $data['spu'];
                        $data_attribute[$key]['attribute_id'] = $key;
                        $data_attribute[$key]['attribute_name'] = isset($array_attributes[$key]) ? $array_attributes[$key]->name : '';
						$data_attribute[$key]['attribute_value'] = $value;
					}
                }
                DB::table('product_to_attribute')->insert($data_attribute);
            }

            // 填充审核记录
            if ($params['status'] != $product->status) {
                DB::table('product_audit_log')->insert(['product_id' => $product_id, 'status' => $request->status]);
            }

            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function delete(Request $request)
    {
        DB::table('product')->where('id', $request->id)->update(['status' => 99]);
        return jsonSuccess();
    }

    public function audit(Request $request)
    {
        $product = DB::table('product')->where('id', $request->id)->first();
        if (empty($product)) return jsonFailed('内容不存在');
        if ($request->isMethod('post')) {
            DB::beginTransaction();
            try {
                DB::table('product')->where('id', $request->id)->update(['status' => $request->status]);
                DB::table('product_audit_log')->insert(['product_id' => $request->id, 'status' => $request->status, 'message' => $request->message]);
                DB::commit();
                return jsonSuccess();
            } catch (\Throwable $th) {
                DB::rollBack();
                return jsonFailed($th->getMessage());
            }
        }
        $logs = DB::table('product_audit_log')->where('product_id', $product->id)->orderBy('id', 'desc')->get()->toArray();
        return view('admin.product.audit', compact('product', 'logs'));
    }

    public function category_list(Request $request)
    {
        $categorys = app(ProductRepository::class)->getCategorys();
        return view('admin.product.category_list', compact('categorys'));
    }

    public function category_create(Request $request)
    {
        $categorys = app(ProductRepository::class)->getCategorys();
        return view('admin.product.category_create', compact('categorys'));
    }

    public function category_store(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = app(ProductRepository::class)->setStoreUpdateParams_category($params);
            $id = DB::table('product_category')->insertGetId($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function category_edit(Request $request)
    {
        $category = DB::table('product_category')->where('id', $request->id)->first();
        if (empty($category)) abort(404);
        $categorys = app(ProductRepository::class)->getCategorys();
        foreach ($categorys as $key => $value) {
            if ($value->id == $category->id) unset($categorys[$key]);
        }
        return view('admin.product.category_edit', compact('category', 'categorys'));
    }

    public function category_update(Request $request)
    {
        DB::beginTransaction();
        try {
            $category = DB::table('product_category')->where('id', $request->id)->where('status', '<>', 99)->first();
            if (empty($category)) return jsonFailed('分类不存在');
            $params = $request->all();
            $data = app(ProductRepository::class)->setStoreUpdateParams_category($params);
            DB::table('product_category')->where('id', $request->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function category_delete(Request $request)
    {
        if (DB::table('product')->where('category_id', $request->id)->first()) return jsonFailed('请先移除该分类下的文档');
        if (DB::table('product_category')->where('parent_id', $request->id)->first()) return jsonFailed('请先移除该分类下的子分类');
        DB::table('product_category')->where('id', $request->id)->delete();
        return jsonSuccess();
    }

    public function specification_group_list(Request $request)
    {
        $params = $request->all();
        $query = DB::table('product_specification_group');
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('product_specification_group.name', 'like', "%" . $params['k'] . "%");
        }
        $query->where('product_specification_group.status', '<>', 99);
        $query->orderBy('product_specification_group.sort', 'desc');
        $groups = $query->paginate();
        $group_ids = array_column($groups->items(), 'id');

        $specifications = DB::table('product_specification')->whereIn('group_id', $group_ids)->where('status', '<>', 99)->get()->toArray();
        $array = [];
        foreach ($specifications as $key => $value) {
            $array[$value->group_id][] = $value;
        }
        foreach ($groups as $key => $value) {
            $groups[$key]->specifications = isset($array[$value->id]) ? $array[$value->id] : [];
        }

        return view('admin.product.specification_group_list', compact('groups'));
    }

    public function specification_group_create(Request $request)
    {
        return view('admin.product.specification_group_create');
    }

    public function specification_group_store(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = app(ProductRepository::class)->setStoreUpdateParams_specificationGroup($params);
            $id = DB::table('product_specification_group')->insertGetId($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function specification_group_edit(Request $request)
    {
        $group = DB::table('product_specification_group')->where('id', $request->id)->first();
        if (empty($group)) abort(404);
        return view('admin.product.specification_group_edit', compact('group'));
    }

    public function specification_group_update(Request $request)
    {
        $group = DB::table('product_specification_group')->where('id', $request->id)->where('status', '<>', 99)->first();
        if (empty($group)) return jsonFailed('内容不存在');
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = app(ProductRepository::class)->setStoreUpdateParams_specificationGroup($params);
            DB::table('product_specification_group')->where('id', $request->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function specification_group_delete(Request $request)
    {
        DB::table('product_specification_group')->where('id', $request->id)->update(['status' => 99]);
        return jsonSuccess();
    }

    public function getSpecifications(Request $request)
    {
        $group_id = $request->group_id;
        $specifications = DB::table('product_specification')->where('group_id', $group_id)->where('status', 1)->get()->toArray();
        $specification_ids = array_column($specifications, 'id');
        $options = DB::table('product_specification_option')->whereIn('specification_id', $specification_ids)->get()->toArray();
        $array = [];
        foreach ($options as $key => $value) {
            $array[$value->specification_id][] = $value;
        }
        foreach ($specifications as $key => $value) {
            $specifications[$key]->options = isset($array[$value->id]) ? $array[$value->id] : [];
        }
        return jsonSuccess($specifications);
    }

    public function specification_list(Request $request)
    {
        $params = $request->all();
        $select = ['product_specification.*', 'product_specification_group.name as group_name'];
        $query = DB::table('product_specification');
        $query->select($select);
        $query->leftJoin('product_specification_group', 'product_specification_group.id', 'product_specification.group_id');

        if (isset($params['group_id']) && !empty($params['group_id'])) {
            $query->where('product_specification.group_id', $params['group_id']);
        }
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('product_specification.name', 'like', "%" . $params['k'] . "%");
        }

        $query->where('product_specification.status', '<>', 99);
        $query->orderBy('product_specification.sort', 'desc')->orderBy('product_specification.id', 'asc');
        $specifications = $query->paginate();

        $specification_ids = array_column($specifications->items(), 'id');
        $options = DB::table('product_specification_option')->whereIn('specification_id', $specification_ids)->get()->toArray();
        $array = [];
        foreach ($options as $key => $value) {
            $array[$value->specification_id][] = $value;
        }
        foreach ($specifications as $key => $value) {
            $specifications[$key]->options = isset($array[$value->id]) ? $array[$value->id] : [];
        }

        $group = DB::table('product_specification_group')->where('id', $request->group_id)->first();
        $groups = DB::table('product_specification_group')
                ->where('product_specification_group.status', '<>', 99)
                ->orderBy('product_specification_group.sort', 'desc')
                ->get()->toArray();
        return view('admin.product.specification_list', compact('specifications', 'group', 'groups'));
    }

    public function specification_create(Request $request)
    {
        $groups = DB::table('product_specification_group')
                ->where('product_specification_group.status', '<>', 99)
                ->orderBy('product_specification_group.sort', 'desc')
                ->get()->toArray();
        return view('admin.product.specification_create', compact('groups'));
    }

    public function specification_store(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = [];
            $data['group_id'] = (isset($params['group_id']) && !empty($params['group_id'])) ? $params['group_id'] : 0;
            if (isset($params['name'])) $data['name'] = $params['name'];
            if (isset($params['sort'])) $data['sort'] = is_numeric($params['sort']) ? $params['sort'] : 0;
            if (isset($params['description'])) $data['description'] = $params['description'];
            if (isset($params['status'])) $data['status'] = $params['status'];
            DB::table('product_specification')->insert($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function specification_edit(Request $request)
    {
        $specification = DB::table('product_specification')->where('id', $request->id)->first();
        if (empty($specification)) abort(404);
        $specification->options = DB::table('product_specification_option')->where('specification_id', $specification->id)->get()->toArray();

        $groups = DB::table('product_specification_group')
                ->where('product_specification_group.status', '<>', 99)
                ->orderBy('product_specification_group.sort', 'desc')
                ->get()->toArray();
        return view('admin.product.specification_edit', compact('specification', 'groups'));
    }

    public function specification_update(Request $request)
    {
        $specification = DB::table('product_specification')->where('id', $request->id)->where('status', '<>', 99)->first();
        if (empty($specification)) return jsonFailed('内容不存在');
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data['group_id'] = (isset($params['group_id']) && !empty($params['group_id'])) ? $params['group_id'] : 0;
            if (isset($params['name'])) $data['name'] = $params['name'];
            if (isset($params['sort'])) $data['sort'] = is_numeric($params['sort']) ? $params['sort'] : 0;
            if (isset($params['description'])) $data['description'] = $params['description'];
            if (isset($params['status'])) $data['status'] = $params['status'];
            DB::table('product_specification')->where('id', $request->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function specification_delete(Request $request)
    {
        DB::table('product_specification')->where('id', $request->id)->update(['status' => 99]);
        return jsonSuccess();
    }

    public function specification_option_list(Request $request)
    {
        $params = $request->all();
        $query = DB::table('product_specification_option');
        $query->select(['product_specification_option.*', 'product_specification.name as specification_name']);
        $query->leftJoin('product_specification', 'product_specification.id', 'product_specification_option.specification_id');
        $query->where('product_specification_option.specification_id', $request->specification_id);
        $query->where('product_specification_option.status', '<>', 99);
        $query->orderBy('product_specification_option.sort', 'desc')->orderBy('product_specification_option.id', 'asc');
        $options = $query->paginate();

        $specification = DB::table('product_specification')->where('id', $request->specification_id)->first();
        return view('admin.product.specification_option_list', compact('options', 'specification'));
    }

    public function specification_option_create(Request $request)
    {
        $specification = DB::table('product_specification')->where('id', $request->specification_id)->first();
        if (empty($specification)) abort(404);
        return view('admin.product.specification_option_create', compact('specification'));
    }

    public function specification_option_store(Request $request)
    {
        $params = $request->all();
        $data = [];
        if (isset($params['option'])) $data['option'] = $params['option'];
        if (isset($params['specification_id'])) $data['specification_id'] = $params['specification_id'];
        if (isset($params['sort'])) $data['sort'] = is_numeric($params['sort']) ? $params['sort'] : 0;
        if (isset($params['status'])) $data['status'] = fileFormat($params['status']);
        DB::table('product_specification_option')->insert($data);
        return jsonSuccess();
    }

    public function specification_option_edit(Request $request)
    {
        $option = DB::table('product_specification_option')->where('id', $request->id)->first();
        if (empty($option)) abort(404);
        $specification = DB::table('product_specification')->where('id', $option->specification_id)->first();
        return view('admin.product.specification_option_edit', compact('option', 'specification'));
    }

    public function specification_option_update(Request $request)
    {
        $option = DB::table('product_specification_option')->where('id', $request->id)->first();
        if (empty($option)) return jsonFailed('内容不存在');
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = [];
            if (isset($params['option'])) $data['option'] = $params['option'];
            if (isset($params['specification_id'])) $data['specification_id'] = $params['specification_id'];
            if (isset($params['sort'])) $data['sort'] = is_numeric($params['sort']) ? $params['sort'] : 0;
            if (isset($params['status'])) $data['status'] = fileFormat($params['status']);
            DB::table('product_specification_option')->where('id', $option->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed();
        }
    }

    public function specification_option_delete(Request $request)
    {
        $option = DB::table('product_specification_option')->where('id', $request->id)->first();
        if (empty($option)) return jsonFailed('内容不存在');
        DB::beginTransaction();
        try {
            DB::table('product_specification_option')->where('id', $request->id)->update(['status' => 99]);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed();
        }
    }

    public function attribute_group_list(Request $request)
    {
        $params = $request->all();
        $query = DB::table('product_attribute_group');
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('product_attribute_group.name', 'like', "%" . $params['k'] . "%");
        }
        $query->where('product_attribute_group.status', '<>', 99);
        $query->orderBy('product_attribute_group.sort', 'desc');
        $groups = $query->paginate();
        $group_ids = array_column($groups->items(), 'id');

        $attributes = DB::table('product_attribute')->whereIn('group_id', $group_ids)->where('status', '<>', 99)->get()->toArray();
        $array = [];
        foreach ($attributes as $key => $value) {
            $array[$value->group_id][] = $value;
        }
        foreach ($groups as $key => $value) {
            $groups[$key]->attributes = isset($array[$value->id]) ? $array[$value->id] : [];
        }

        return view('admin.product.attribute_group_list', compact('groups'));
    }

    public function attribute_group_create(Request $request)
    {
        return view('admin.product.attribute_group_create');
    }

    public function attribute_group_store(Request $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = app(ProductRepository::class)->setStoreUpdateParams_attributeGroup($params);
            $id = DB::table('product_attribute_group')->insertGetId($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function attribute_group_edit(Request $request)
    {
        $group = DB::table('product_attribute_group')->where('id', $request->id)->first();
        if (empty($group)) abort(404);
        return view('admin.product.attribute_group_edit', compact('group'));
    }

    public function attribute_group_update(Request $request)
    {
        $group = DB::table('product_attribute_group')->where('id', $request->id)->where('status', '<>', 99)->first();
        if (empty($group)) return jsonFailed('内容不存在');
        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = app(ProductRepository::class)->setStoreUpdateParams_attributeGroup($params);
            DB::table('product_attribute_group')->where('id', $request->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function attribute_group_delete(Request $request)
    {
        DB::table('product_attribute_group')->where('id', $request->id)->update(['status' => 99]);
        return jsonSuccess();
    }

    public function getAttributes(Request $request)
    {
        $group_id = $request->group_id;
        $attributes = DB::table('product_attribute')->where('group_id', $group_id)->where('status', 1)->get()->toArray();
        $attribute_ids = array_column($attributes, 'id');
        $options = DB::table('product_attribute_option')->whereIn('attribute_id', $attribute_ids)->get()->toArray();
        $array = [];
        foreach ($options as $key => $value) {
            $array[$value->attribute_id][] = $value;
        }
        foreach ($attributes as $key => $value) {
            $attributes[$key]->options = isset($array[$value->id]) ? $array[$value->id] : [];
        }
        return jsonSuccess($attributes);
    }

    public function attribute_list(Request $request)
    {
        $params = $request->all();
        $select = ['product_attribute.*', 'product_attribute_group.name as group_name'];
        $query = DB::table('product_attribute');
        $query->select($select);
        $query->leftJoin('product_attribute_group', 'product_attribute_group.id', 'product_attribute.group_id');

        if (isset($params['group_id']) && !empty($params['group_id'])) {
            $query->where('product_attribute.group_id', $params['group_id']);
        }
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('product_attribute.name', 'like', "%" . $params['k'] . "%");
        }

        $query->where('product_attribute.status', '<>', 99);
        $query->orderBy('product_attribute.sort', 'desc')->orderBy('product_attribute.id', 'asc');
        $attributes = $query->paginate();

        $attribute_ids = array_column($attributes->items(), 'id');
        $options = DB::table('product_attribute_option')->whereIn('attribute_id', $attribute_ids)->get()->toArray();
        $array = [];
        foreach ($options as $key => $value) {
            $array[$value->attribute_id][] = $value;
        }
        foreach ($attributes as $key => $value) {
            $attributes[$key]->options = isset($array[$value->id]) ? $array[$value->id] : [];
        }

        $group = DB::table('product_attribute_group')->where('id', $request->group_id)->first();
        $groups = DB::table('product_attribute_group')
                ->where('product_attribute_group.status', '<>', 99)
                ->orderBy('product_attribute_group.sort', 'desc')
                ->get()->toArray();
        return view('admin.product.attribute_list', compact('attributes', 'group', 'groups'));
    }

    public function attribute_create(Request $request)
    {
        $groups = DB::table('product_attribute_group')
                ->where('product_attribute_group.status', '<>', 99)
                ->orderBy('product_attribute_group.sort', 'desc')
                ->get()->toArray();
        return view('admin.product.attribute_create', compact('groups'));
    }

    public function attribute_store(Request $request)
    {
        $rules = [
            'name' => ['required'],
            'type' => ['required', Rule::in(Config('common.mall.product_attribute_type'))],
        ];
        $messages = [
            'name.required' => '属性名字不能为空',
            'type.required' => '类型不能为空',
            'type.in' => '类型错误',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return jsonFailed($validator->errors()->first());

        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = [];
            if (isset($params['group_id'])) $data['group_id'] = is_numeric($params['group_id']) ? $params['group_id'] : 0;
            if (isset($params['name'])) $data['name'] = $params['name'];
            if (isset($params['type'])) $data['type'] = $params['type'];
            if (isset($params['sort'])) $data['sort'] = is_numeric($params['sort']) ? $params['sort'] : 0;
            if (isset($params['description'])) $data['description'] = $params['description'];
            if (isset($params['required'])) $data['required'] = $params['required'];
            if (isset($params['status'])) $data['status'] = $params['status'];
            DB::table('product_attribute')->insert($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function attribute_edit(Request $request)
    {
        $attribute = DB::table('product_attribute')->where('id', $request->id)->first();
        if (empty($attribute)) abort(404);

        $attribute->options = DB::table('product_attribute_option')->where('attribute_id', $attribute->id)->orderBy('sort', 'desc')->get()->toArray();

        $groups = DB::table('product_attribute_group')
                ->where('product_attribute_group.status', '<>', 99)
                ->orderBy('product_attribute_group.sort', 'desc')
                ->get()->toArray();
        return view('admin.product.attribute_edit', compact('attribute', 'groups'));
    }

    public function attribute_update(Request $request)
    {
        $rules = [
            'name' => ['required'],
            'type' => ['required', Rule::in(Config('common.mall.product_attribute_type'))],
        ];
        $messages = [
            'name.required' => '属性名字不能为空',
            'type.required' => '类型不能为空',
            'type.in' => '类型错误',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return jsonFailed($validator->errors()->first());

        DB::beginTransaction();
        try {
            $attribute = DB::table('product_attribute')->where('id', $request->id)->where('status', '<>', 99)->first();
            if (empty($attribute)) return jsonFailed('内容不存在');
            $params = $request->all();
            if (isset($params['group_id'])) $data['group_id'] = is_numeric($params['group_id']) ? $params['group_id'] : 0;
            if (isset($params['name'])) $data['name'] = $params['name'];
            if (isset($params['type'])) $data['type'] = $params['type'];
            if (isset($params['sort'])) $data['sort'] = is_numeric($params['sort']) ? $params['sort'] : 0;
            if (isset($params['description'])) $data['description'] = $params['description'];
            if (isset($params['required'])) $data['required'] = $params['required'];
            if (isset($params['status'])) $data['status'] = $params['status'];
            DB::table('product_attribute')->where('id', $request->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function attribute_delete(Request $request)
    {
        DB::table('product_attribute')->where('id', $request->id)->update(['status' => 99]);
        return jsonSuccess();
    }

    public function attribute_option_list(Request $request)
    {
        $params = $request->all();
        $query = DB::table('product_attribute_option');
        $query->select(['product_attribute_option.*', 'product_attribute.name as attribute_name']);
        $query->leftJoin('product_attribute', 'product_attribute.id', 'product_attribute_option.attribute_id');
        $query->where('product_attribute_option.attribute_id', $request->attribute_id);
        $query->where('product_attribute_option.status', '<>', 99);
        $query->orderBy('product_attribute_option.sort', 'desc')->orderBy('product_attribute_option.id', 'asc');
        $options = $query->paginate();

        $attribute = DB::table('product_attribute')->where('id', $request->attribute_id)->first();
        return view('admin.product.attribute_option_list', compact('options', 'attribute'));
    }

    public function attribute_option_create(Request $request)
    {
        $attribute = DB::table('product_attribute')->where('id', $request->attribute_id)->first();
        if (empty($attribute)) abort(404);
        return view('admin.product.attribute_option_create', compact('attribute'));
    }

    public function attribute_option_store(Request $request)
    {
        $params = $request->all();
        $data = [];
        if (isset($params['option'])) $data['option'] = $params['option'];
        if (isset($params['attribute_id'])) $data['attribute_id'] = $params['attribute_id'];
        if (isset($params['sort'])) $data['sort'] = is_numeric($params['sort']) ? $params['sort'] : 0;
        if (isset($params['status'])) $data['status'] = fileFormat($params['status']);
        DB::table('product_attribute_option')->insert($data);
        return jsonSuccess();
    }

    public function attribute_option_edit(Request $request)
    {
        $option = DB::table('product_attribute_option')->where('id', $request->id)->first();
        if (empty($option)) abort(404);
        $attribute = DB::table('product_attribute')->where('id', $option->attribute_id)->first();
        return view('admin.product.attribute_option_edit', compact('option', 'attribute'));
    }

    public function attribute_option_update(Request $request)
    {
        $option = DB::table('product_attribute_option')->where('id', $request->id)->first();
        if (empty($option)) return jsonFailed('内容不存在');

        DB::beginTransaction();
        try {
            $params = $request->all();
            $data = [];
            if (isset($params['option'])) $data['option'] = $params['option'];
            if (isset($params['attribute_id'])) $data['attribute_id'] = $params['attribute_id'];
            if (isset($params['sort'])) $data['sort'] = is_numeric($params['sort']) ? $params['sort'] : 0;
            if (isset($params['status'])) $data['status'] = fileFormat($params['status']);
            DB::table('product_attribute_option')->where('id', $option->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed();
        }
    }

    public function attribute_option_delete(Request $request)
    {
        $option = DB::table('product_attribute_option')->where('id', $request->id)->first();
        if (empty($option)) return jsonFailed('内容不存在');

        DB::beginTransaction();
        try {
            DB::table('product_attribute_option')->where('id', $request->id)->update(['status' => 99]);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed();
        }
    }
}
