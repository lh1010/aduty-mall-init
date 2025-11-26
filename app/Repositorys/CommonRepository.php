<?php

namespace App\Repositorys;

use DB;
use Carbon\Carbon;
use App\Repositorys\ProductRepository;

class CommonRepository
{
    public function getAdver($code)
    {
        $adver = DB::table('adver')->where(['status' => 1, 'code' => $code])->first();
        if (empty($adver)) return $adver;
        $values = DB::table('adver_value')->where('adver_id', $adver->id)->orderBy('sort', 'desc')->orderBy('id', 'asc')->get()->toArray();
        foreach ($values as $key => $value) {
            $values[$key]->image = fileView($value->image);
        }
        $adver->values = $values;
        return $adver;
    }

    // 电脑web端首页所需数据
    public function getIndexData_pc()
    {
        $data = [];

        // post data
        $ProductRepository = new ProductRepository;
        $categorys = DB::table('product_category')
                    ->select(['product_category.id', 'product_category.name'])
                    ->where('product_category.parent_id', 0)
                    ->where('product_category.status', 1)
                    ->get()->toArray();
        foreach ($categorys as $key => $value) {
            $child_ids = $ProductRepository->getCategoryChildIds($value->id);
            $categorys[$key]->products = $ProductRepository->getList(['category_ids' => $child_ids, 'status' => 1, 'updown_status' => 1], $type = 'get', 10);
        }

        $data['product_data'] = $categorys;

        $data['adver_banner'] = [];
        $adver = $this->getAdver('pc_index_banner');
        if (!empty($adver)) $data['adver_banner'] = $adver->values;

        return $data;
    }
}
