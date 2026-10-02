<?php

namespace App\Repositorys;

use DB;

class ProductRepository
{
    public function getList($params = [], $type = 'paginate', $limit = 15)
    {
        $select = ['product.*'];
        $query = DB::table('product');
        $query->select($select);
        $this->setParams($query, $params);
        if ($type == 'paginate') {
            $products = $query->paginate($limit);
            $ids = array_column($products->items(), 'id');
        } else {
            if ($limit > 0 ) $query->limit($limit);
            $products = $query->get()->toArray();
            $ids = array_column($products, 'id');
        }

        $product_skus = DB::table('product_sku')->whereIn('product_id', $ids)->orderBy('price', 'asc')->get()->toArray();
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
            $products[$key]->cover = !empty($value->cover) ? fileView($value->cover) : Config('common.image.product_cover');
        }

        return $products;
    }

    public function setParams($query, $params = [])
    {
        $query->where('product.status', '<>', 99);

        if (!empty($params['k'])) {
            $query->where('product.name', 'like', '%' . $params['k'] . '%');
        }

        if (!empty($params['category_id'])) {
            $query->where('product.category_id', $params['category_id']);
        }

        if (!empty($params['category_ids'])) {
            $query->whereIn('product.category_id', $params['category_ids']);
        }

        if (!empty($params['status'])) {
            $query->where('product.status', $params['status']);
        }

        if (!empty($params['random'])) {
            $query->inRandomOrder();
        }

        // 排序
        if (isset($params['order'])) {
            if ($params['order'] == '最新') {
                $query->orderBy('product.created_at', 'desc');
            }
            $db_prefix = env('DB_PREFIX', '');
            if ($params['order'] == '价格最低') {
                $query->orderByRaw('(SELECT MIN(ps.price) FROM ' . $db_prefix . 'product_sku ps WHERE ps.product_id = ' . $db_prefix . 'product.id) asc');
            }
            if ($params['order'] == '价格最高') {
                $query->orderByRaw('(SELECT MIN(ps.price) FROM ' . $db_prefix . 'product_sku ps WHERE ps.product_id = ' . $db_prefix . 'product.id) desc');
            }
            if ($params['order'] == '最新') {
                $query->orderBy('product.created_at', 'desc');
            }
        }

        // 默认排序
        $query->orderBy('product.created_at', 'desc');
    }

    /**
     * 获取商品详情
     * @param int $id 商品ID
     * @param array $params 查询参数
     * @return array|null
     */
    public function getShow($id, $params = [])
    {
        $query = DB::table('product');
        $query->select(['product.*', 'product_category.name as category_name']);
        $query->leftJoin('product_category', 'product_category.id', 'product.category_id');
        $query->where('product.id', $id);
        if (isset($params['status']) && !empty($params['status'])) {
            $query->where('product.status', $params['status']);
        }
        $product = $query->first();
        if (empty($product)) return null;

        $product->full_category_name = $this->getFullCategoryName($product->category_id); // 完整分类名称
        $product->cover = !empty($product->cover) ? fileView($product->cover) : Config('common.image.product_cover');
        $preg = "/<img(.*?)src=\"(.*?)\"(.*?)>/is";
        if (preg_match_all($preg, $product->content, $matches)) {
            foreach ($matches[2] as $key => $value) {
                if (strstr($value, 'http')) {
                    $new_img_url = $value;
                    $new_img_url = str_replace(Config('common.app_url'), '', $new_img_url);
                    $new_img_url = str_replace(Config('common.oss.url'), '', $new_img_url);
                    $product->content = str_replace($value, $new_img_url, $product->content);
                }
            }
            $url = Config('common.app_url');
            if (Config('common.oss.status')) $url = Config('common.oss.url');
            $product->content = preg_replace($preg, '<img onclick="showImage(\'' . $url . '$2\')" src="' . $url . '$2" />', $product->content);
        }

        // images
        $images = DB::table('product_image')->where('product_id', $id)->get()->toArray();
        foreach ($images as $key => $value) {
            $images[$key]->image = fileView($value->image);
        }
        $product->images = $images;

        // 商品属性
        $attributes = DB::table('product_to_attribute')->where('product_id', $id)->get()->toArray();
        $product->attributes = $attributes;

        // 所有sku 当前商品(spu)下的所有sku
        $allSkus = DB::table('product_sku')->where('product_id', $id)->get()->keyBy('sku'); // 以sku为键，便于快速查找

        // 获取该商品下所有规格关联记录 product_to_specification
        $allSpecs = DB::table('product_to_specification')->where('product_id', $id)->get();

        // 构建[sku->规格选项ID集合]的映射 用于快速匹配
        $skuOptionMap = [];
        foreach ($allSpecs as $spec) {
            $skuOptionMap[$spec->sku][] = (int)$spec->specification_option_id;
        }

        // 当前sku 优先使用传入的sku
        if (!empty($params['sku']) && isset($allSkus[$params['sku']])) {
            $currentSku = $allSkus[$params['sku']];
        } else {
            // 排序规则：有库存优先 → 价格低优先
            $currentSku = $allSkus->sortBy(function ($sku) {
                return [$sku->stock <= 0, $sku->price];
            })->first();
        }

        // 当前选中的规格选项ID列表
        $currentOptionIds = $skuOptionMap[$currentSku->sku] ?? [];

        // 构建规格分组结构 按规格ID=specification_id分组
        $grouped = [];
        foreach ($allSpecs as $spec) {
            $gid = $spec->specification_id;
            if (!isset($grouped[$gid])) {
                $grouped[$gid] = [
                    'specification_id'   => $gid,
                    'specification_name' => $spec->specification_name,
                    'options'            => []
                ];
            }
            // 去重：同一个规格选项可能出现在多个SKU中，但只需保留一个选项记录
            $oid = $spec->specification_option_id;
            if (!isset($grouped[$gid]['options'][$oid])) {
                $grouped[$gid]['options'][$oid] = [
                    'specification_option_id'   => $oid,
                    'specification_option'      => $spec->specification_option,
                    'specification_id'          => $gid,
                    'specification_name'        => $spec->specification_name,
                    'selected' => 0,
                    'valid'    => 0,
                    'sku'      => null,
                    'stock'    => 0,
                ];
            }
        }

        // 遍历每个规格组中的每个选项，判断其[有效性]和[选中状态]
        foreach ($grouped as &$group) {
            $gid = $group['specification_id'];
            foreach ($group['options'] as &$option) {
                $oid = $option['specification_option_id'];
                // 判断是否选中：当前sku包含该选项
                if (in_array($oid, $currentOptionIds)) {
                    $option['selected'] = 1;
                }
                // 判断是否有效(可点击)
                // 存在至少一个sku，其规格选项集合包含[当前已选的所有选项(排除本组) + 当前选项]，且该sku库存 > 0
                // 构建[假设已选]的选项ID数组：将当前选中选项中属于本组的全部去掉，再加入当前选项
                $assumedSelected = [];
                foreach ($currentOptionIds as $selectedId) {
                    // 查找该选项属于哪个规格组
                    $selectedSpecId = null;
                    foreach ($grouped as $g) {
                        if (isset($g['options'][$selectedId])) {
                            $selectedSpecId = $g['specification_id'];
                            break;
                        }
                    }
                    // 如果属于本组则跳过
                    if ($selectedSpecId == $gid) {
                        continue;
                    }
                    $assumedSelected[] = $selectedId;
                }
                $assumedSelected[] = $oid; // 加入当前选项
                // 遍历所有sku，检查是否存在一个sku包含全部$assumedSelected选项且库存>0
                $valid = false;
                $skuForOption = null;
                $stockForOption = 0;
                foreach ($skuOptionMap as $sku => $optionIds) {
                    // 判断$assumedSelected是否被$optionIds完全包含 即交集等于$assumedSelected
                    if (empty(array_diff($assumedSelected, $optionIds))) {
                        $valid = true;
                        $skuForOption = $sku;
                        $stockForOption = $allSkus[$sku]->stock ?? 0;
                        break;
                    }
                }
                $option['valid'] = $valid ? 1 : 0;
                if ($valid) {
                    $option['sku'] = $skuForOption;
                    $option['stock'] = $stockForOption;
                }
            }
        }
        unset($group, $option); // 解除引用

        // 转换数据结构 将option从关联数组转为索引数组，便于前端循环
        $specifications = [];
        foreach ($grouped as $group) {
            $group['options'] = array_values($group['options']);
            $specifications[] = $group;
        }

        // 获取当前sku所关联的规格明细
        $currentSpecs = DB::table('product_to_specification')
            ->where('sku', $currentSku->sku)
            ->get()
            ->toArray();
        $currentSku->specifications = $currentSpecs;

        // 将当前sku和规格分组挂载到商品对象
        $currentSku->cover = !empty($currentSku->cover) ? fileView($currentSku->cover) : Config('common.image.product_cover');
        $product->sku = $currentSku;
        $product->specifications = $specifications;

        // skus收藏
        $collectSkus = [];
        $loginUser = getLoginUser();
        if (!empty($loginUser)) {
            $skus = $allSkus->pluck('sku')->toArray();
            $collectSkus = DB::table('user_collect_product')->where('user_id', $loginUser->id)->whereIn('sku', $skus)->pluck('sku')->toArray();
        }

        // 构建前端需要的sku列表数据结构
        $skuListForFrontend = [];
        foreach ($allSkus as $key => $value) {
            $skuListForFrontend[] = [
                'sku'           => $key,
                'price'         => $value->price,
                'stock'         => $value->stock,
                'cover'         => fileView($value->cover),
                'option_ids'    => $skuOptionMap[$key] ?? [],
                'collect_status'=> in_array($key, $collectSkus) ? 1 : 0,
            ];
        }
        $product->skus = $skuListForFrontend;

        return $product;
    }

    /**
     * 获取下级分类ID集合
     * @param int $id 分类ID
     */
    public function getCategoryChildIds($id)
	{
        $categorys = DB::table('product_category')->select('id', 'parent_id')->get()->toArray();
        $childrenMap = [];
        foreach ($categorys as $category) {
            $childrenMap[$category->parent_id][] = $category->id;
        }

        $result = [];
        $stack = [$id]; // 用数组模拟栈，初始放入根ID

        while (!empty($stack)) {
            $parentId = array_pop($stack); // 弹出一个父ID
            $result[] = (int)$parentId; // 加入结果
            // 如果该父ID有子分类，全部压入栈
            if (isset($childrenMap[$parentId])) {
                foreach ($childrenMap[$parentId] as $childId) {
                    $stack[] = $childId;
                }
            }
        }
        return $result;
    }

    /**
     * 获取上级分类ID集合
     * @param int $id 分类ID
     */
    public function getCategoryParentIds($id)
    {
        $result = [];
        if ($id <= 0) return $result;
        $categorys = DB::table('product_category')->select('id', 'parent_id')->get()->keyBy('id')->toArray();
        $current = $id;
        while ($current > 0 && isset($categorys[$current])) {
            $result[] = (int)$current;
            $parent_id = $categorys[$current]->parent_id;
            if ($parent_id == 0) {
                break;
            }
            $current = $parent_id;
        }
        $result = array_reverse($result);
        return $result;
    }

    /**
     * 获取完整分类名称
     * @param int $id 分类ID
     */
    public function getFullCategoryName($id)
	{
		$parent_ids = $this->getCategoryParentIds($id);
		$categorys = DB::table('product_category')->whereIn('id', $parent_ids)->pluck('name')->toArray();
		$full_category_name = implode(' > ', $categorys);
		return $full_category_name;
	}
}
