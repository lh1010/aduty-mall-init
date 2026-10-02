<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use DB;
use App\Repositorys\ProductRepository;
use App\Repositorys\OrderRepository;

class ProductController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('CheckUserLogin')->except([
            'getList',
            'getShow',
            'getCategorys',
            'getCategory',
            'getCartCount',
            'getNewProducts'
        ]);
    }

    public function getList(Request $request)
    {
        $productRepository = new ProductRepository;
        $params = $request->all();
        if (isset($params['category_id']) && !empty($params['category_id'])) {
            $category_ids = $productRepository->getCategoryChildIds($params['category_id']);
            $params['category_ids'] = $category_ids;
            unset($params['category_id']);
        }
        $params['status'] = 1;
        $page_size = $request->input('page_size', 15);
        $products = $productRepository->getList($params, $type = 'paginate', $limit = $page_size);
        return jsonSuccess($products);
    }

    public function getShow(Request $request)
    {
        $loginUser = getLoginUser();
        $ProductRepository = new ProductRepository;
        $params = $request->all();
        $params['status'] = 1;
        $product = $ProductRepository->getShow($request->id, $params);
        if (empty($product)) return jsonFailed('商品不存在');
        // 收藏
        $product->collect_status = 0;
        if (!empty($loginUser)) {
            //$collect = DB::table('user_collect_product')->where(['user_id' => $loginUser->id, 'sku' => $product->sku->sku])->first();
            // if (!empty($collect)) {
            //     $product->collect_status = 1;
            // }
        }
        return jsonSuccess($product);
    }

    public function getCategorys(Request $request)
    {
        $allCategorys = DB::table('product_category')->where('status', 1)->orderBy('sort', 'desc')->orderBy('id', 'asc')->get()->toArray();
        $grouped = [];
        foreach ($allCategorys as $item) {
            $grouped[$item->parent_id][] = $item;
        }
        $topCategorys = $grouped[0] ?? [];
        foreach ($topCategorys as $topKey => $top) {
            $twoCategorys = $grouped[$top->id] ?? [];
            foreach ($twoCategorys as $twoKey => $two) {
                $twoCategorys[$twoKey]->items = $grouped[$two->id] ?? [];
            }
            $topCategorys[$topKey]->items = $twoCategorys;
        }
        return jsonSuccess($topCategorys);
    }

    public function getCategory(Request $request)
    {
        $category = DB::table('product_category')->where(['status' => 1, 'id' => $request->id])->first();
        return jsonSuccess($category);
    }

    public function addCart(Request $request)
    {
        $sku = $request->input('sku', '');
        $count = $request->input('count', 1);
        $product_sku = DB::table('product_sku')->where('sku', $sku)->first();
        if (empty($product_sku)) return jsonFailed('该商品已下架');
        $user = $request->get('user');
        $cart = DB::table('cart')->where('sku', $sku)->where('user_id', $user->id)->first();
        if (!empty($cart)) {
            DB::table('cart')->where('id', $cart->id)->increment('count', $count);
        } else {
            DB::table('cart')->insert([
                'user_id' => $user->id,
                'product_id' => $product_sku->product_id,
                'sku' => $sku,
                'count' => $count
            ]);
        }
        return jsonSuccess([], 200, '已添加至购物车');
    }

    public function deleteCart(Request $request)
    {
        $user = $request->get('user');
        $sku = $request->input('sku', '');
        $skus = $request->input('skus', []);
        if (!empty($skus)) {
            DB::table('cart')->whereIn('sku', $skus)->where('user_id', $user->id)->delete();
        } else {
            DB::table('cart')->where('sku', $sku)->where('user_id', $user->id)->delete();
        }
        return jsonSuccess();
    }

    public function getCartCount(Request $request)
    {
        $count = 0;
        $user = getLoginUser();
        if (!empty($user)) {
            $count = DB::table('cart')->where('user_id', $user->id)->count();
        }
        return jsonSuccess($count);
    }

    public function setCartSelected(Request $request)
    {
        $user = $request->get('user');
        $sku = $request->input('sku', '');
        $selected = $request->input('selected', 0);
        DB::table('cart')->where('user_id', $user->id)->where('sku', $sku)->update(['selected' => $selected]);
        $totalData = app(OrderRepository::class)->getCartTotal($user);
        return jsonSuccess($totalData);
    }

    public function setCartSelectedBatch(Request $request)
    {
        $user = $request->get('user');
        $skus = is_array($request->skus) ? $request->skus : explode(',', $request->skus);
        $selected = $request->input('selected', 0);
        DB::table('cart')->where('user_id', $user->id)->whereIn('sku', $skus)->update(['selected' => $selected]);
        $totalData = app(OrderRepository::class)->getCartTotal($user);
        return jsonSuccess($totalData);
    }

    public function collect(Request $request)
    {
        $loginUser = $request->get('user');
        $sku = $request->input('sku', '');
        $res = DB::table('user_collect_product')->where(['user_id' => $loginUser->id, 'sku' => $sku])->first();
        $collect = 0;
        if (empty($res)) {
            DB::table('user_collect_product')->insert(['user_id' => $loginUser->id, 'sku' => $sku]);
        }
        return jsonSuccess(['collect' => $collect]);
    }

    public function deleteCollect(Request $request)
    {
        $loginUser = $request->get('user');
        $sku = $request->input('sku', '');
        DB::table('user_collect_product')->where(['user_id' => $loginUser->id, 'sku' => $sku])->delete();
        return jsonSuccess();
    }

    public function getCollectProducts(Request $request)
    {
        $page_size = $request->input('page_size', 15);
        $loginUser = $request->get('user');
        $select = ['user_collect_product.*', 'product.id as product_id', 'product.name as product_name', 'product_sku.sku', 'product_sku.price', 'product_sku.cover', 'product_sku.stock'];
        $query = DB::table('user_collect_product');
        $query->select($select);
        $query->leftJoin('product_sku', 'user_collect_product.sku', 'product_sku.sku');
        $query->leftJoin('product', 'product.id', 'product_sku.product_id');
        $query->where('user_collect_product.user_id', $loginUser->id);
        $products = $query->paginate($page_size);
        foreach ($products as $key => $value) {
            $products[$key]->cover = !empty($value->cover) ? fileView($value->cover) : Config('common.image.product_cover');
        }
        $skus = array_column($products->items(), 'sku');
        // 规格
        $product_to_specifications = DB::table('product_to_specification')->whereIn('sku', $skus)->get()->toArray();
        $array = [];
        foreach ($product_to_specifications as $key => $value) {
            $array[$value->sku][] = $value;
        }
        foreach ($products as $key => $value) {
            $products[$key]->specifications = $array[$value->sku] ?? [];
        }
        return jsonSuccess($products);
    }

    public function getNewProducts(Request $request)
    {
        $params = $request->all();
        $params['status'] = 1;
        $params['random'] = 1;
        $products = app(ProductRepository::class)->getList($params, $type = 'get', 10);
        return jsonSuccess($products);
    }
}
