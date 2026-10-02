<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use DB;
use App\Repositorys\AdverRepository;
use App\Repositorys\ProductRepository;

class CommonController extends BaseController
{
    public function getMenus(Request $request)
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

        return jsonSuccess($menuData);
    }

    public function getIndexSections()
    {
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
        return jsonSuccess($categorys);
    }

    // 帮助中心
    public function getHelps()
    {
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
        return jsonSuccess($categorys);
    }

    public function getCitys()
    {
        $citys = DB::table('city')
            ->where('level', 2)
            ->where('shortname', '<>', '')
            ->where('status', 1)
            ->get()->toArray();
        $citys = object_to_array($citys);
        $citys = arraySort($citys, 'first');
        $citys = arrayGroup($citys, 'first');
        return jsonSuccess($citys);
    }

    public function getCityList(Request $request)
    {
        $pid = $request->input('pid', 0);
        $query = DB::table('city');
        $query->where('pid', $pid);
        $citys = $query->get()->toArray();
        return jsonSuccess($citys);
    }

    /**
     * 获取区域数据
     * @return json
     */
    public function getRegionOptions(Request $request)
    {
        $citys = DB::table('city')->orderBy('sort', 'desc')->orderBy('id', 'asc')->get()->toArray();
        $result = $this->buildTree($citys, 0);
        return jsonSuccess($result);
    }

    private function buildTree($data, $pid = 0)
    {
        $tree = [];
        foreach ($data as $item) {
            if ($item->pid == $pid) {
                $children = $this->buildTree($data, $item->id);
                $node = ['value' => $item->id, 'label' => $item->name];
                if (!empty($children)) $node['children'] = $children;
                $tree[] = $node;
            }
        }
        return $tree;
    }

    // 获取所有配置
    public function getConfig()
    {
        $config = Config('common');
        // 删除部分配置数据
        unset($config['oss']);
        unset($config['sms']);
        unset($config['wxapp']['appid']);
        unset($config['wxapp']['secret']);
        unset($config['wxmp']['appid']);
        unset($config['wxmp']['secret']);
        unset($config['weixinpay']);
        unset($config['alipay']);
        return jsonSuccess($config);
    }

    // 获取单条广告数据
    public function getAdver(Request $request)
    {
        $adver = app(AdverRepository::class)->getAdver($request->code);
        return jsonSuccess($adver);
    }

    // APP版本更新
    public function versionUpdate(Request $request)
    {
        $sys = [];
        $config_common = Config('common');
        if (isset($config_common[Request()->request_client])) {
            $sys = $config_common[Request()->request_client];
            if (isset($sys['version_list'][Request()->app_version]) && $sys['version_list'][Request()->app_version]) {
                $sys = array_merge($sys, $sys['version_list'][Request()->app_version]);
            }
            unset($sys['version_list']);
        }
        return jsonSuccess($sys);
    }
}
