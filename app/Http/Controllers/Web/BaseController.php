<?php

namespace App\Http\Controllers\Web;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use DB;

class BaseController extends Controller
{
    public function __construct()
    {
        $this->getMenuData();
        $this->getFootData();
    }

    private function getMenuData()
    {
        $menuData = [];

        $categorys = DB::table('product_category')->where(['status' => 1, 'parent_id' => 0])->orderBy('sort', 'desc')->orderBy('id', 'asc')->get()->toArray();
        $parent_ids = array_column($categorys, 'id');

        $two_categorys = DB::table('product_category')->whereIn('parent_id', $parent_ids)->where('status', 1)->orderBy('sort', 'desc')->orderBy('id', 'asc')->get()->toArray();
        $parent_ids = array_column($two_categorys, 'id');

        $three_categorys = DB::table('product_category')->whereIn('parent_id', $parent_ids)->where('status', 1)->orderBy('sort', 'desc')->orderBy('id', 'asc')->get()->toArray();

        $array = [];
        foreach ($three_categorys as $key => $value) {
            $array[$value->parent_id][] = $value;
        }
        foreach ($two_categorys as $key => $value) {
            $two_categorys[$key]->items = isset($array[$value->id]) ? $array[$value->id] : [];
        }

        $array = [];
        foreach ($two_categorys as $key => $value) {
            $array[$value->parent_id][] = $value;
        }
        foreach ($categorys as $key => $value) {
            $categorys[$key]->items = isset($array[$value->id]) ? $array[$value->id] : [];
        }

        $menuData = $categorys;

        // 其他数据组装
        // ......

        View()->share('menuData', $menuData);
    }

    private function getFootData()
    {
        $footData = [];

        // 帮助中心
        $categorys = DB::table('article_category')->where('parent_id', 100001)->where('status', 1)->orderBy('sort', 'desc')->get()->toArray();
        $category_id = array_column($categorys, 'id');
        $articles = DB::table('article')->whereIn('category_id', $category_id)->where('status', 1)->orderBy('sort', 'desc')->get()->toArray();
        $array = [];
        foreach ($articles as $key => $value) {
            $array[$value->category_id][] = $value;
        }
        foreach ($categorys as $key => $value) {
            $categorys[$key]->articles = isset($array[$value->id]) ? $array[$value->id] : [];
        }
        $footData['helps'] = $categorys;

        View()->share('footData', $footData);
    }
}
