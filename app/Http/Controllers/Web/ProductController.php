<?php

namespace App\Http\Controllers\Web;

use DB;
use Illuminate\Http\Request;
use App\Repositorys\ProductRepository;

class ProductController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->middleware('CheckUserLogin')->only(['preview']);
    }

    public function list(Request $request, $id = '')
    {
        $ProductRepository = new ProductRepository;
        $params = $request->all();
        $category = [];
        if (!empty($id)) {
            $category = DB::table('product_category')->where('id', $id)->first();
            $child_ids = $ProductRepository->getCategoryChildIds($id);
            $params['category_ids'] = $child_ids;
        }
        $params['status'] = 1;
        $products = $ProductRepository->getList($params);
        $nav = '';
        $nav .= '<span class="on">当前位置：</span>';
        $nav .= '<a href="/">首页</a>';
        $nav .= '<span>></span>';
        if (!empty($category)) {
            $nav .= '<span class="on">' . $category->name . '</span>';
        } else if (isset($params['k']) && !empty($params['k'])) {
            $nav .= '<span class="on">搜索</span>';
            $nav .= '<span>></span>';
            $nav .= '<span class="on">' . $params['k'] . '</span>';
        } else {
            $nav .= '<span class="on">全部商品</span>';
        }

        $data_assign = [
            'products' => $products,
            'category' => $category,
            'nav' => $nav
        ];

        return view(Config('common.view.tpl_folder') . '.product.list', $data_assign);
    }

    public function show(Request $request, $sku)
    {
        $loginUser = getLoginUser();
        $ProductRepository = new ProductRepository;
        $product = $ProductRepository->getShow($sku, ['sku' => $sku, 'status' => 1]);
        if (empty($product)) return abort(404);

        $product->collect_status = 0;
        if (!empty($loginUser)) {
            $collect = DB::table('user_collect_product')->where(['user_id' => $loginUser->id, 'sku' => $sku])->first();
            if (!empty($collect)) {
                $product->collect_status = 1;
            }
        }

        $category = DB::table('product_category')->where('id', $product->category_id)->first();

        // 获取最新商品
        $r1 = $ProductRepository->getList([], $type = 'get', 8);

        $data_assign = [
            'product' => $product,
            'category' => $category,
            'r1' => $r1,
        ];

        return view(Config('common.view.tpl_folder') . '.product.show', $data_assign);
    }

    public function preview(Request $request)
    {
        $user = $request->get('user');
        $ProductRepository = new ProductRepository;
        $product = $ProductRepository->getProduct($request->id);
        if (empty($product)) return abort(404);
        $user_login = getLoginUser();

        $is_collect = 0;
        if (!empty($user_login)) {
            $res = DB::table('user_collect_product')->where('product_id', $product->id)->where('user_id', $user_login->id)->first();
            if (!empty($res)) $is_collect = 1;
        }

        $category = DB::table('product_category')->where('id', $product->category_id)->first();

        // 店铺
        $shop = app(ShopRepository::class)->getShop($product->shop_id);

        // 获取最新商品
        $r1 = $ProductRepository->getProducts([], 8);

        $data_assign = [
            'product' => $product,
            'is_collect' => $is_collect,
            'category' => $category,
            'shop' => $shop,
            'r1' => $r1,
        ];

        // 验证商品归属
        $loginShop = DB::table('shop')->where('user_id', $user->id)->first();
        if (empty($loginShop)) return redirect(url('/'));
        if ($loginShop->id != $product->shop_id) return redirect(url('/'));

        return view(Config('common.view.tpl_folder') . '.product.preview', $data_assign);
    }
}
