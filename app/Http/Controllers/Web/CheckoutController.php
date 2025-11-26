<?php

namespace App\Http\Controllers\Web;

use DB;
use Illuminate\Http\Request;
use App\Repositorys\OrderRepository;

class CheckoutController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct();
        $this->middleware('CheckUserLogin');
    }

    public function onekeybuy(Request $request)
    {
        $loginUser = $request->get('user');
        $params = $request->all();
        $params['type'] = 'onekeybuy';
        $params['user'] = $loginUser;
        $res = app(OrderRepository::class)->getCheckoutData($params);
        $checkoutData = $res['data'];
        return view(Config('common.view.tpl_folder') . '.checkout.onekeybuy', compact('checkoutData'));
    }

    public function cart(Request $request)
    {
        $user = $request->get('user');
        $params = $request->all();
        $params['type'] = 'cart';
        $params['user'] = $user;
        $res = app(OrderRepository::class)->getCheckoutData($params);
        if ($res['code'] != 200) return redirect(url('/cart'));
        $checkoutData = $res['data'];
        $checkoutData['type'] = 'cart';
        return view(Config('common.view.tpl_folder') . '.checkout.cart', compact('checkoutData'));
    }

    public function pay(Request $request)
    {
        $user = $request->get('user');
        $order_ids = explode(',', $request->order_ids);
        $orders = DB::table('order')->where(['user_id' => $user->id, 'status' => 0])->whereIn('id', $order_ids)->get()->toArray();
        if (empty($orders)) abort(404);
        $totalData = ['total_price' => 0.00];
        foreach ($orders as $key => $value) {
            $totalData['total_price'] = bcadd($totalData['total_price'], $value->total_price, 2);
        }
        return view(Config('common.view.tpl_folder') . '.checkout.pay', compact('orders', 'totalData', 'user'));
    }
}
