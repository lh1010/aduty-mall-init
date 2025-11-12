<?php

namespace App\Repositorys;

use DB;

class ProductRepository
{
    public function getList($params = [], $type = 'paginate', $limit = 16)
    {
        $select = ['product.*'];
        $query = DB::table('product');
        $query->select($select);
        $this->setParams($query, $params);
        $query->where('product.status', '<>', 99);
        $query->orderBy('product.created_at', 'desc');
        if ($type == 'paginate') {
            $products = $query->paginate($limit);
            $ids = array_column($products->items(), 'id');
        } else {
            if ($limit > 0 ) $query->limit($limit);
            $products = $query->get()->toArray();
            $ids = array_column($products, 'id');
        }

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
            $products[$key]->cover = !empty($value->cover) ? fileView($value->cover) : Config('common.image.product_cover');
        }

        return $products;
    }

    public function setParams($query, $params = [])
    {
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where('product.name', 'like', '%' . $params['k'] . '%');
        }

        if (isset($params['category_id']) && !empty($params['category_id'])) {
            $query->where('product.category_id', $params['category_id']);
        }

        if (isset($params['category_ids']) && !empty($params['category_ids'])) {
            $query->whereIn('product.category_id', $params['category_ids']);
        }

        if (isset($params['status']) && !empty($params['status'])) {
            $query->where('product.status', $params['status']);
        }
    }

    public function getShow($sku, $params = [])
    {
        $product_sku = DB::table('product_sku')->where('sku', $sku)->first();
        if (empty($product_sku)) return null;

        $id = $product_sku->product_id;
        $query = DB::table('product');
        $query->where('product.id', $id);
        if (isset($params['status']) && !empty($params['status'])) {
            $query->where('product.status', $params['status']);
        }
        $product = $query->first();
        if (empty($product)) return null;

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

        // 单规格
        if ($product->specification_type == '单规格') {
            $product_sku = DB::table('product_sku')->where('product_id', $id)->first();
            $product_sku->cover = !empty($product_sku->cover) ? fileView($product_sku->cover) : Config('common.image.product_cover');
            $product->sku = $product_sku;
        }

        // 多规格
        $product->specifications = [];
        if ($product->specification_type == '多规格') {
            // 当前sku信息
            if (isset($params['sku']) && !empty($params['sku'])) {
                $product_sku = DB::table('product_sku')->where('sku', $params['sku'])->first();
            } else {
                $product_skus = DB::table('product_sku')->where('product_id', $id)->get()->toArray();
                $product_sku = $product_skus[0];
            }
            $product_sku->cover = !empty($product_sku->cover) ? fileView($product_sku->cover) : Config('common.image.product_cover');
            // 当前sku下的销售规格
            $current_specifications = DB::table('product_to_specification')->where('sku', $product_sku->sku)->get()->toArray();
            $current_specification_ids = array_column($current_specifications, 'specification_id');
            $current_specification_option_ids = array_column($current_specifications, 'specification_option_id');
            $product_sku->specifications = $current_specifications;
            $product->sku = $product_sku;
            // 当前商品下的销售规格
            $product_to_specifications = DB::table('product_to_specification')
                ->select(['product_to_specification.*', 'product_sku.stock'])
                ->leftJoin('product_sku', 'product_sku.sku', 'product_to_specification.sku')
                ->where('product_to_specification.product_id', $id)
                ->get()->toArray();
            // 当前商品下的skus
            $product_skus = DB::table('product_sku')->where('product_id', $id)->get()->toArray();
            foreach ($product_skus as $key => $value) {
                $product_skus[$key]->cover = !empty($value->cover) ? fileView($value->cover) : Config('common.image.product_cover');
            }
            $array = [];
            foreach ($product_to_specifications as $key => $value) {
                $array[$value->sku][] = $value;
            }
            foreach ($product_skus as $key => $value) {
                $product_skus[$key]->specifications = isset($array[$value->sku]) ? $array[$value->sku] : [];
            }
            $product->skus = $product_skus;
            // 分配销售规格组
            $skus = [];
            foreach ($product_to_specifications as $key => $value) {
                $skus[$value->sku][] = $value;
            }
            // 获取与当前销售规格有关联的sku
            // 获取与当前销售规格有关联的商品关联规格ID
            // 设置商品关联规格是否有效/可点击
            $have_product_skus = [];
            $have_product_to_specification_ids = [];
            foreach ($product_to_specifications as $key => $value) {
                if (count($current_specification_option_ids) > 1) {
                    if (in_array($value->specification_option_id, $current_specification_option_ids)) {
                        $current_sku_specification_option_ids = array_column($skus[$value->sku], 'specification_option_id');
                        $the_same_date_count = array_intersect($current_sku_specification_option_ids, $current_specification_option_ids);
                        if (count($the_same_date_count) >= count($current_specification_option_ids) - 1) {
                            $have_product_skus[] = $value->sku;
                            $have_product_to_specification_ids[] = $value->id;
                        }
                    }
                } else {
                    $have_product_skus[] = $value->sku;
                    $have_product_to_specification_ids[] = $value->id;
                }
            }
            foreach ($product_to_specifications as $key => $value) {
                $product_to_specifications[$key]->valid = 0;
                if (in_array($value->sku, $have_product_skus)) {
                    $product_to_specifications[$key]->valid = 1;
                }
            }
            // 组装数据
            $array = [];
            foreach ($product_to_specifications as $key => $value) {
                $array[$value->specification_id]['specification_id'] = $value->specification_id;
                $array[$value->specification_id]['specification_name'] = $value->specification_name;
                $array[$value->specification_id]['options'][$value->specification_option_id]['specification_option_id'] = $value->specification_option_id;
                $array[$value->specification_id]['options'][$value->specification_option_id]['specification_option'] = $value->specification_option;
                $array[$value->specification_id]['options'][$value->specification_option_id]['specification_id'] = $value->specification_id;
                $array[$value->specification_id]['options'][$value->specification_option_id]['specification_name'] = $value->specification_name;
                if ($value->valid == 1) {
                    $array[$value->specification_id]['options'][$value->specification_option_id]['valid'] = $value->valid;
                    $array[$value->specification_id]['options'][$value->specification_option_id]['sku'] = $value->sku;
                    $array[$value->specification_id]['options'][$value->specification_option_id]['stock'] = $value->stock;
                }
                if ($value->sku == $product_sku->sku) {
                    $array[$value->specification_id]['options'][$value->specification_option_id]['selected'] = 1;
                }
            }
            foreach ($array as $key => $value) {
                $array[$key]['options'] = array_values($value['options']);
            }
            $array = array_values($array);
            $product->specifications = $array;
        }

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
