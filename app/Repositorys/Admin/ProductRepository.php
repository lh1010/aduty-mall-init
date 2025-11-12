<?php

namespace App\Repositorys\Admin;

use DB;

class ProductRepository
{
    public function setCreateUpdateParams($params = [])
    {
        $data = [];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['category_id'])) $data['category_id'] = $params['category_id'];
        if (isset($params['cover'])) $data['cover'] = fileFormat($params['cover']);
        if (isset($params['description'])) $data['description'] = $params['description'];
        if (isset($params['content'])) $data['content'] = $params['content'];

        $data['price'] = 0;
        $preg = "/^[1-9]+\d*(.\d{1,2})?$|^\d+.\d{1,2}$/";
        if (isset($params['price']) && preg_match($preg, $params['price'])) {
            $data['price'] = $params['price'];
        }

        if (isset($params['stock'])) $data['stock'] = is_numeric($params['stock']) ? $params['stock'] : 0;
        if (isset($params['shipment_time'])) $data['shipment_time'] = $params['shipment_time'];
        if (isset($params['transport_way'])) $data['transport_way'] = $params['transport_way'];
        if (isset($params['free_shipping'])) $data['free_shipping'] = $params['free_shipping'];
        if (isset($params['freight_tpl_id'])) $data['freight_tpl_id'] = is_numeric($params['freight_tpl_id']) ? $params['freight_tpl_id'] : 0;
        if (isset($params['specification_type'])) $data['specification_type'] = $params['specification_type'];
        if (isset($params['specification_group_id'])) $data['specification_group_id'] = is_numeric($params['specification_group_id']) ? $params['specification_group_id'] : 0;
        if (isset($params['attribute_group_id'])) $data['attribute_group_id'] = is_numeric($params['attribute_group_id']) ? $params['attribute_group_id'] : 0;
        if (isset($params['images_type'])) $data['images_type'] = $params['images_type'];
        if (isset($params['status'])) $data['status'] = is_numeric($params['status']) ? $params['status'] : 0;

        $data['seo_title'] = isset($params['seo_title']) && !empty($params['seo_title']) ? $params['seo_title'] : $params['name'];
        $data['seo_keywords'] = isset($params['seo_keywords']) && !empty($params['seo_keywords']) ? $params['seo_keywords'] : $params['name'];
        $data['seo_description'] = isset($params['seo_description']) && !empty($params['seo_description']) ? $params['seo_description'] : $params['name'];
        return $data;
    }

    public function setStoreUpdateParams_category($params = [])
    {
        $data = [];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['parent_id'])) $data['parent_id'] = $params['parent_id'];
        if (isset($params['cover'])) $data['cover'] = fileFormat($params['cover']);
        if (isset($params['description'])) $data['description'] = $params['description'];
        if (isset($params['sort']) && is_numeric($params['sort'])) $data['sort'] = $params['sort'];
        if (isset($params['status'])) $data['status'] = $params['status'];
        $data['seo_title'] = isset($params['seo_title']) && !empty($params['seo_title']) ? $params['seo_title'] : $params['name'];
        $data['seo_keywords'] = isset($params['seo_keywords']) && !empty($params['seo_keywords']) ? $params['seo_keywords'] : $params['name'];
        $data['seo_description'] = isset($params['seo_description']) && !empty($params['seo_description']) ? $params['seo_description'] : $params['name'];
        return $data;
    }

    public function getCategory($id)
    {
        $query = DB::table('product_category');
        $query->where('id', $id);
        $query->where('status', '<>', 99);
        $category = $query->first();
        if (empty($category)) return $category;

        // 商品模型
        // if (!empty($category->model_id)) {
        //     $model = DB::table('product_model')->where('id', $category->model_id)->where('status', 1)->first();

        //     $specifications = DB::table('product_specification')->where('model_id', $category->model_id)->where('status', 1)->get()->toArray();
        //     $specification_ids = array_column($specifications, 'id');
        //     $options = DB::table('product_specification_option')->whereIn('specification_id', $specification_ids)->get()->toArray();
        //     $array = [];
        //     foreach ($options as $key => $value) {
		// 		$array[$value->specification_id][] = $value;
		// 	}
        //     foreach ($specifications as $key => $value) {
        //         $specifications[$key]->options = isset($array[$value->id]) ? $array[$value->id] : [];
        //     }

        //     $attributes = DB::table('product_attribute')->where('model_id', $category->model_id)->get()->toArray();
        //     $attribute_ids = array_column($attributes, 'id');
        //     $options = DB::table('product_attribute_option')->whereIn('attribute_id', $attribute_ids)->get()->toArray();
        //     $array = [];
        //     foreach ($options as $key => $value) {
		// 		$array[$value->attribute_id][] = $value;
		// 	}
        //     foreach ($attributes as $key => $value) {
        //         $attributes[$key]->options = isset($array[$value->id]) ? $array[$value->id] : [];
        //     }

        //     $category->model = $model;
        //     $category->specifications = $specifications;
        //     $category->attributes = $attributes;
        // }

        return $category;
    }

    private $treeList = [];
    public function getCategorys($params = [], $type = 'tree')
    {
        $query = DB::table('product_category');
        $query->select(['product_category.*']);
        if (isset($params['status'])) $query->where('product_category.status', $params['status']);
        if (isset($params['parent_id'])) $query->where('product_category.parent_id', $params['parent_id']);
        $query->where('product_category.status', '<>', 99);
        $query->orderBy('product_category.sort', 'desc');
        $query->orderBy('product_category.id', 'asc');
        $categorys = $query->get()->toArray();
        switch ($type) {
            case 'select':
                return $categorys;
                break;
            case 'tree':
                $categorys = $this->tree($categorys);
                return $categorys;
                break;
        }
    }

    private function tree($data, $parent_id = 0, $level = 1)
    {
        foreach ($data as $value){
            if ($value->parent_id == $parent_id) {
                $value->level = $level;
                $this->treeList[] = $value;
                $this->tree($data, $value->id, $level + 1);
            }
        }
        return $this->treeList;
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

    public function setStoreUpdateParams_specificationGroup($params = [])
    {
        $data = [];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['description'])) $data['description'] = $params['description'];
        if (isset($params['sort'])) $data['sort'] = is_numeric($params['sort']) ? $params['sort'] : 0;
        if (isset($params['status'])) $data['status'] = fileFormat($params['status']);
        return $data;
    }

    public function setStoreUpdateParams_attributeGroup($params = [])
    {
        $data = [];
        if (isset($params['name'])) $data['name'] = $params['name'];
        if (isset($params['description'])) $data['description'] = $params['description'];
        if (isset($params['sort'])) $data['sort'] = is_numeric($params['sort']) ? $params['sort'] : 0;
        if (isset($params['status'])) $data['status'] = fileFormat($params['status']);
        return $data;
    }
}
