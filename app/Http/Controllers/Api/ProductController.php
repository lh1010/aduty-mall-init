<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use DB;
use App\Repositorys\ProductRepository;

class ProductController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('CheckUserLogin')->except([
            'getList',
            'getShow',
            'getCategorys',
            'getCategory'
        ]);
    }

    public function getList(Request $request)
    {
        $params = $request->all();
        if (isset($params['category_id']) && !empty($params['category_id'])) {
            $category_ids = app(ProductRepository::class)->getCategoryChildIds($params['category_id']);
            $params['category_ids'] = $category_ids;
            unset($params['category_id']);
        }
        $params['status'] = 1;
        $products = app(ProductRepository::class)->getList($params);
        return jsonSuccess($products);
    }

    public function getShow(Request $request)
    {
        $sku = $request->sku;
        $params = $request->all();
        $params['status'] = 1;
        $product = app(ProductRepository::class)->getShow($sku, $params);
        return jsonSuccess($product);
    }

    public function getCategorys(Request $request)
    {
        $params = $request->all();
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

        return jsonSuccess($categorys);
    }

    public function getCategory(Request $request)
    {
        $category = DB::table('product_category')->where(['status' => 1, 'id' => $request->id])->first();
        return jsonSuccess($category);
    }

    public function addCart(Request $request)
    {
        $product_sku = DB::table('product_sku')->where('sku', $request->sku)->first();
        if (empty($product_sku)) return jsonFailed('该商品已下架');
        $user = $request->get('user');
        $cart = DB::table('cart')->where('sku', $request->sku)->where('user_id', $user->id)->first();
        if (!empty($cart)) {
            DB::table('cart')->where('id', $cart->id)->increment('count', $request->count);
        } else {
            DB::table('cart')->insert([
                'user_id' => $user->id,
                'product_id' => $product_sku->product_id,
                'sku' => $product_sku->sku,
                'count' => $request->count
            ]);
        }
        return jsonSuccess([], 200, '已添加至购物车');
    }

    public function deleteCart(Request $request)
    {
        $user = $request->get('user');
        $skus = explode(',', $request->skus);
        DB::table('cart')->whereIn('sku', $skus)->where('user_id', $user->id)->delete();
        return jsonSuccess();
    }

    public function selectCart(Request $request)
    {
        $user = $request->get('user');
        $skus = explode(',', $request->skus);
        DB::table('cart')->where('user_id', $user->id)->whereIn('sku', $skus)->update(['selected' => 1]);
        DB::table('cart')->where('user_id', $user->id)->whereNotIn('sku', $skus)->update(['selected' => 0]);
        return jsonSuccess();
    }
}
