<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;
use App\Repositorys\Admin\OrderRepository;

class OrderController extends BaseController
{
    public function list(Request $request)
    {
        $params = $request->all();
        $orders = app(OrderRepository::class)->getList($params);
        return view('admin.order.list', compact('orders'));
    }

    public function show(Request $request)
    {
        $order = app(OrderRepository::class)->getShow($request->id);
        return view('admin.order.show', compact('order'));
    }

    // 取消订单
    public function cancelOrder(Request $request)
    {
        $order = DB::table('order')->where(['id' => $request->id])->first();
        if (empty($order)) return jsonFailed('订单不存在');
        // if ($order->status != 0) return jsonFailed('订单状态已更新，请刷新当前页面');
        DB::table('order')->where(['id' => $order->id])->update(['status' => -10]);
        DB::table('order_log')->insert(['order_id' => $order->id, 'content' => '商家已取消订单']);
        return jsonSuccess();
    }

    // 订单发货
    public function shipmentOrder(Request $request)
    {
        $order = DB::table('order')->where(['order.id' => $request->id])->first();
        if (empty($order)) return jsonFailed('订单不存在');
        if ($request->isMethod('post')) {
            $data = [
                'status' => 20,
                'shipping_company' => $request->shipping_company,
                'tracking_number' => $request->tracking_number,
            ];
            DB::table('order')->where('id', $order->id)->update($data);
            DB::table('order_log')->insert(['order_id' => $order->id, 'content' => '商家已发货']);
            return jsonSuccess();
        }
        return view('admin.order.shipmentOrder', compact('order'));
    }

    // 确认收货
    public function receiveOrder(Request $request)
    {
        $order = DB::table('order')->where(['order.id' => $request->id])->first();
        if (empty($order)) return jsonFailed('订单不存在');
        DB::table('order')->where('id', $order->id)->update(['status' => 30]);
        DB::table('order_log')->insert(['order_id' => $order->id, 'content' => '已确认收货 - 商家操作']);
        return jsonSuccess();
    }
}
