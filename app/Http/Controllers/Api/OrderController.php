<?php

namespace App\Http\Controllers\Api;

use DB;
use Illuminate\Http\Request;
use App\Repositorys\OrderRepository;

class OrderController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('CheckUserLogin');
    }

    public function getList(Request $request)
    {
        $user = $request->get('user');
        $params = $request->all();
        $params['user_id'] = $user->id;
        $orders = app(OrderRepository::class)->getList($params);
        return jsonSuccess($orders);
    }

    public function getShow(Request $request)
    {
        $user = $request->get('user');
        $params = $request->all();
        $params['user_id'] = $user->id;
        $order = app(OrderRepository::class)->getShow($params);
        return jsonSuccess($order);
    }

    public function getCartData(Request $request)
    {
        $user = $request->get('user');
        $cartData = app(OrderRepository::class)->getCartData($user);
        return jsonSuccess($cartData);
    }

    public function getCheckoutData(\App\Http\Requests\Order\getCheckoutData $request)
    {
        $user = $request->get('user');
        $params = $request->all();
        $params['user'] = $user;
        $checkoutData = app(OrderRepository::class)->getCheckoutData($params);
        return $checkoutData;
    }

    public function getOrderPayData(Request $request)
    {
        $user = $request->get('user');
        $params['user_id'] = $user->id;
        $params['ids'] = explode(',', $request->order_ids);
        $orders = app(OrderRepository::class)->getList($params, $type = 'get');
        $totalData = ['total_price' => 0.00];
        foreach ($orders as $key => $value) {
            $totalData['total_price'] = bcadd($totalData['total_price'], $value->total_price, 2);
        }
        $data = ['orders' => $orders, 'totalData' => $totalData];
        return jsonSuccess($data);
    }

    public function createOrder(\App\Http\Requests\Order\createOrder $request)
    {
        $user = $request->get('user');
        $params = $request->all();
        $params['user'] = $user;
        $OrderRepository = new OrderRepository();
        $checkoutDataRes = $OrderRepository->getCheckoutData($params);
        if ($checkoutDataRes['code'] != 200) return $res;
        $checkoutData = $checkoutDataRes['data'];
        $products = $checkoutData['products'] ?? [];
        $address = $params['address'] ?? [];
        $totalData = $checkoutData['totalData'] ?? [];

        DB::beginTransaction();
        try {
            // 订单数据
            $data_order = [];
            $data_order['user_id'] = $user->id;
            $data_order['name'] = $checkoutData['address']['name'];
            $data_order['phone'] = $checkoutData['address']['phone'];
            $data_order['province_name'] = $checkoutData['address']['province_name'];
            $data_order['city_name'] = $checkoutData['address']['city_name'];
            $data_order['district_name'] = $checkoutData['address']['district_name'];
            $data_order['detailed_address'] = $checkoutData['address']['detailed_address'];
            $data_order['product_total_price'] = $totalData['product_total_price'];
            $data_order['total_price'] = $totalData['total_price'];
            $order_id = DB::table('order')->insertGetId($data_order);
            $number = createOrderNumber($order_id);
            DB::table('order')->where('id', $order_id)->update(['number' => $number]);

            // 快照数据
            $data_snap = [];
            foreach ($products as $key => $value) {
                $specifications = $value['specification_type'] == '多规格' ? json_encode($value['specifications']) : '';
                $data_snap[$key]['order_id'] = $order_id;
                $data_snap[$key]['product_id'] = $value['id'];
                $data_snap[$key]['spu'] = $value['spu'];
                $data_snap[$key]['sku'] = $value['sku'];
                $data_snap[$key]['name'] = $value['name'];
                $data_snap[$key]['cover'] = fileFormat($value['cover']);
                $data_snap[$key]['count'] = $value['count'];
                $data_snap[$key]['price'] = $value['price'];
                $data_snap[$key]['total_price'] = $value['total_price'];
                $data_snap[$key]['specifications'] = $specifications;
            }
            DB::table('order_snap')->insert($data_snap);

            // 删除购物车已选中产品
            if ($params['type'] == 'cart') {
                DB::table('cart')->where(['user_id' => $user->id, 'selected' => 1])->delete();
            }

            DB::table('order_log')->insert(['order_id' => $order_id, 'content' => '买家已下单']);
            DB::commit();
            return jsonSuccess(['order_ids' => [$order_id]]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

     /**
     * 取消订单
     * 买家取消
     */
    public function cancelOrder(Request $request)
    {
        $user = $request->get('user');
        $order = DB::table('order')->where(['id' => $request->order_id, 'user_id' => $user->id])->first();
        if (empty($order)) return jsonFailed('订单不存在');
        if ($order->status != 0) return jsonFailed('订单状态已更新，请刷新当前页面');
        DB::table('order')->where(['id' => $order->id])->update(['status' => -10]);
        DB::table('order_log')->insert(['order_id' => $order->id, 'content' => '买家已取消订单']);
        return jsonSuccess();
    }
}
