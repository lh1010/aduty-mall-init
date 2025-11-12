<?php

namespace App\Repositorys;

use DB;
use Jenssegers\Agent\Agent;

class OrderRepository
{
    public function getList($params = [], $type = 'paginate', $limit = 15)
    {
        $select = ['order.*', 'user.nickname as user_nickname'];
        $query = DB::table('order');
        $query->select($select);
        $query->leftJoin('user', 'user.id', 'order.user_id');
        $this->setParams($query, $params);
        if ($type == 'paginate') {
            $orders = $query->paginate($limit);
            $order_ids = array_column($orders->items(), 'id');
        } else {
            if ($limit >= 0 ) $query->limit($limit);
            $orders = $query->get()->toArray();
            $order_ids = array_column($orders, 'id');
        }

        $snaps = DB::table('order_snap')->whereIn('order_id', $order_ids)->get()->toArray();
        $array = [];
        foreach ($snaps as $key => $value) {
            $value->cover = !empty($value->cover) ? fileView($value->cover) : Config('common.image.product_cover');
            $value->specifications = !empty($value->specifications) ? json_decode($value->specifications) : [];
            $array[$value->order_id][] = $value;
        }

        foreach ($orders as $key => $value) {
            $orders[$key]->status_str = Config('common.mall.order_status')[$value->status];
            $orders[$key]->snaps = isset($array[$value->id]) ? $array[$value->id] : [];
        }
        return $orders;
    }

    private function setParams($query, $params = [])
    {
        $query->where('order.status', '<>', 99);

        if (isset($params['status']) && !empty($params['status'])) {
            $query->where('order.status', $params['status']);
        }

        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $query->where('order.user_id', $params['user_id']);
        }

        if (isset($params['k']) && !empty($params['k'])) {
            if ($params['kident'] == '订单编号') {
                $query->where('order.number', $params['k']);
            }
            if ($params['kident'] == '商品名字') {
                $order_ids = DB::table('order_snap')->where('product_name', 'like', "%" . $params['k'] . "%")->pluck('order_id')->toArray();
                $query->whereIn('order.id', $order_ids);
            }
        }

        if (isset($params['ids']) && !empty($params['ids'])) {
            $query->whereIn('order.id', $params['ids']);
        }
        $query->orderBy('order.created_at', 'desc');
    }

    /**
     * getOrder
     * @param int $id order id
     * @param int $params['user_id']
     */
    public function getShow($id, $params = [])
    {
        $select = ['order.*', 'user.nickname as user_nickname'];
        $query = DB::table('order');
        $query->select($select);
        $query->leftJoin('user', 'user.id', 'order.user_id');
        $query->where('order.id', $id);
        $query->where('order.status', '<>', 99);

        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $query->where('order.user_id', $params['user_id']);
        }

        $order = $query->first();
        if (empty($order)) return $order;
        $order->status_str = Config('common.mall.order_status')[$order->status];

        $snaps = DB::table('order_snap')->where('order_id', $order->id)->get()->toArray();
        foreach ($snaps as $key => $value) {
            $snaps[$key]->cover = !empty($value->cover) ? fileView($value->cover) : Config('common.image.product_cover');
            $snaps[$key]->specifications = !empty($value->specifications) ? json_decode($value->specifications) : [];
        }
        $order->snaps = $snaps;

        // 订单日志
        $order->logs = DB::table('order_log')->where('order_id', $order->id)->orderBy('created_at', 'asc')->get()->toArray();
        return $order;
    }

    /**
     * getCheckoutData
     * @param int $params['type']
     * @param int $params['address_id']
     * @param object $params['user'] login user
     * @return $checkoutData['products']
     * @return $checkoutData['address']
     * @return $checkoutData['totalData']
     */
    public function getCheckoutData($params = [])
    {
        $data = [];
        $user = $params['user'];
        $type = $params['type'];
        $products = [];

        // 购买的商品
        if ($type == 'cart') $res = $this->getCartData($user, ['selected' => 1]);
        if ($type == 'onekeybuy') $res = $this->getOneKeyBuyData($user, $params);
        $products = $res['products'];
        if (empty($products)) return arrayFailed('商品不存在');
        $data['products'] = $products;

        // 收货地址
        if (isset($params['address_id']) && !empty($params['address_id'])) {
            $address = DB::table('user_address')->where(['id' => $params['address_id'], 'user_id' => $user->id])->first();
        } else {
            $address = DB::table('user_address')->where(['default' => 1, 'user_id' => $user->id])->first();
            if (empty($address)) {
                $address = DB::table('user_address')->where(['user_id' => $user->id])->orderBy('id', 'desc')->first();
            }
        }
        $data['address'] = $address;

        // 合计
        $totalData = [
            'product_count' => count($products),
            'product_total_price' => '0.00',
            'total_price' => 0.00
        ];
        foreach ($products as $key => $value) {
            $totalData['product_total_price'] = bcadd($totalData['product_total_price'], $value['total_price'], 2);
            $totalData['total_price'] = bcadd($totalData['total_price'], $value['total_price'], 2);
        }
        $data['totalData'] = $totalData;

        $data = object_to_array($data);
        return arraySuccess($data);
    }

    /**
     * getOneKeyBuyData
     * @param object $user
     * @param int $params['sku']
     */
    public function getOneKeyBuyData($user, $params = [])
    {
        $select = ['product.*', 'product_sku.sku', 'product_sku.price', 'product_sku.cover', 'product_sku.stock'];
        $query = DB::table('product');
        $query->select($select);
        $query->leftJoin('product_sku', 'product.id', 'product_sku.product_id');
        $query->where('product_sku.sku', $params['sku']);
        $query->where('product.status', 1);
        $products = $query->get()->toArray();
        $skus = array_column($products, 'sku');

        foreach ($products as $key => $value) {
            $products[$key]->cover = !empty($value->cover) ? fileView($value->cover) : Config('common.image.product_cover');
            $count = $params['count'] ?? 1;
            $products[$key]->count = $count;
            $products[$key]->total_price = bcmul($value->price, $count, 2);
        }

        // 规格
        $product_to_specifications = DB::table('product_to_specification')->whereIn('sku', $skus)->get()->toArray();
        $array = [];
        foreach ($product_to_specifications as $key => $value) {
            $array[$value->sku][] = $value;
        }
        foreach ($products as $key => $value) {
            $products[$key]->specifications = $array[$value->sku] ?? [];
        }

        $data = ['products' => $products];
        $data = object_to_array($data);
        return $data;
    }

    /**
     * getCartData
     * @param object $user
     * @param $params 客户端提交参数 系统定制化
     * @return $products
     * @return $totalData
     * @return $shops
     */
    public function getCartData($user, $params = [])
    {
        $select = ['cart.selected', 'cart.count', 'product.*', 'product_sku.sku', 'product_sku.price', 'product_sku.cover', 'product_sku.stock'];
        $query = DB::table('cart');
        $query->select($select);
        $query->leftJoin('product', 'product.id', 'cart.product_id');
        $query->leftJoin('product_sku', 'cart.sku', 'product_sku.sku');
        $query->where('cart.user_id', $user->id);
        if (isset($params['selected'])) $query->where('cart.selected', $params['selected']);
        $products = $query->get()->toArray();
        foreach ($products as $key => $value) {
            $products[$key]->total_price = bcmul($value->price, $value->count, 2);
        }
        $skus = array_column($products, 'sku');

        // 规格
        $product_to_specifications = DB::table('product_to_specification')->whereIn('sku', $skus)->get()->toArray();
        $array = [];
        foreach ($product_to_specifications as $key => $value) {
            $array[$value->sku][] = $value;
        }
        foreach ($products as $key => $value) {
            $products[$key]->specifications = $array[$value->sku] ?? [];
        }

        $totalData = ['total_price' => 0, 'all_selected' => 1];
        foreach ($products as $key => $value) {
            $products[$key]->cover = !empty($value->cover) ? fileView($value->cover) : Config('common.image.product_cover');
            if ($value->selected == 1) $totalData['total_price'] = bcadd(($value->price * $value->count), $totalData['total_price'], 2);
            if ($value->selected != 1) $totalData['all_selected'] = 0;
        }

        $data = ['products' => $products, 'totalData' => $totalData];
        $data = object_to_array($data);
        return $data;
    }
}
