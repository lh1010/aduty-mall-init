<?php

namespace App\Http\Controllers\Web;

use DB;
use Illuminate\Http\Request;
use App\Repositorys\ProductRepository;
use App\Repositorys\ShopRepository;
use App\Repositorys\OrderRepository;
use App\Repositorys\CommonRepository;
use App\Repositorys\TaskRepository;
use App\Repositorys\UserRepository;

class AccountController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->middleware('CheckUserLogin')->except([
            'login',
            'login_password',
            'register'
        ]);
    }

    public function login_password()
    {
        return view(Config('common.view.tpl_folder') . '.account.login_password');
    }

    public function login()
    {
        return view(Config('common.view.tpl_folder') . '.account.login');
    }

    public function register()
    {
        return view(Config('common.view.tpl_folder') . '.account.register');
    }

    public function index()
    {
        return redirect('/account/user_info');
        return view(Config('common.view.tpl_folder') . '.account.index');
    }

    public function user_info(Request $request)
    {
        $user = $request->get('user');
        $citys = DB::table('city')
            ->where('level', 2)
            ->where('shortname', '<>', '')
            ->where('status', 1)
            ->get()->toArray();
        return view(Config('common.view.tpl_folder') . '.account.user_info', compact('user', 'citys'));
    }

    public function user_password(Request $request)
    {
        $user = $request->get('user');
        $menu_ident = 'user_info';
        return view(Config('common.view.tpl_folder') . '.account.user_password', compact('user', 'menu_ident'));
    }

    public function user_contact(Request $request)
    {
        $user = $request->get('user');
        $menu_ident = 'user_info';
        $user_contact = DB::table('user_contact')->where('user_id', $user->id)->first();
        return view(Config('common.view.tpl_folder') . '.account.user_contact', compact('user', 'user_contact', 'menu_ident'));
    }

    public function wallet(Request $request)
    {
        $user = $request->get('user');
        $user_wallet_logs = DB::table('user_wallet_log')->where('user_id', $user->id)->orderBy('id', 'desc')->limit(15)->get()->toArray();
        return view(Config('common.view.tpl_folder') . '.account.wallet', compact('user', 'user_wallet_logs'));
    }

    public function wallet_log(Request $request)
    {
        $user = $request->get('user');
        $user_wallet_logs = DB::table('user_wallet_log')->where('user_id', $user->id)->orderBy('id', 'desc')->paginate();

        $menu_ident = 'wallet';
        return view(Config('common.view.tpl_folder') . '.account.wallet_log', compact('menu_ident', 'user', 'user_wallet_logs'));
    }

    public function wallet_pay(Request $request)
    {
        $user = $request->get('user');
        $menu_ident = 'wallet';
        return view(Config('common.view.tpl_folder') . '.account.wallet_pay', compact('menu_ident', 'user'));
    }

    public function wallet_withdraw(Request $request)
    {
        $user = $request->get('user');
        $withdrawal_logs = DB::table('user_wallet_withdrawal_log')->where('user_id', $user->id)->orderBy('id', 'desc')->paginate();

        $menu_ident = 'wallet';
        return view(Config('common.view.tpl_folder') . '.account.wallet_withdraw', compact('menu_ident', 'user', 'withdrawal_logs'));
    }

    public function order_list(Request $request)
    {
        $user = $request->get('user');
        $params = $request->all();
        $params['user_id'] = $user->id;
        $orders = app(OrderRepository::class)->getList($params);
        return view(Config('common.view.tpl_folder') . '.account.order_list', compact('orders'));
    }

    public function order_show(Request $request)
    {
        $user = $request->get('user');
        $order = app(OrderRepository::class)->getShow($request->id, ['user_id' => $user->id]);
        if (empty($order)) abort(404);
        return view(Config('common.view.tpl_folder') . '.account.order_show', compact('order'));
    }

    public function realname_auth(Request $request)
    {
        $user = $request->get('user');
        return view(Config('common.view.tpl_folder') . '.account.realname_auth', compact('user'));
    }

    public function company_auth(Request $request)
    {
        $user = $request->get('user');
        return view(Config('common.view.tpl_folder') . '.account.company_auth', compact('user'));
    }

    public function collect_product(Request $request)
    {
        $loginUser = $request->get('user');
        $select = ['user_collect_product.*', 'product.name', 'product_sku.sku', 'product_sku.price', 'product_sku.cover', 'product_sku.stock'];
        $query = DB::table('user_collect_product');
        $query->select($select);
        $query->leftJoin('product_sku', 'user_collect_product.sku', 'product_sku.sku');
        $query->leftJoin('product', 'product.id', 'product_sku.product_id');
        $query->where('user_collect_product.user_id', $loginUser->id);
        $products = $query->paginate();
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

        return view(Config('common.view.tpl_folder') . '.account.collect_product', compact('products'));
    }
}
