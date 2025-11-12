<?php

namespace App\Repositorys\Admin;

use DB;

class OrderRepository
{
    public function getList($params = [], $type = 'paginate', $limit = 15)
    {
        $select = ['order.*', 'user.nickname as user_nickname'];
        $query = DB::table('order');
        $query->select($select);
        $query->leftJoin('user', 'order.user_id', 'user.id');
        $this->setParams($query, $params);
        $query->where('order.status', '<>', 99);
        $query->orderBy('order.created_at', 'desc');

        if ($type == 'paginate') {
            $orders = $query->paginate($limit);
        } else {
            if ($limit > 0 ) $query->limit($limit);
            $orders = $query->get()->toArray();
        }

        return $orders;
    }

    public function setParams($query, $params = [])
    {
        if (isset($params['k']) && !empty($params['k'])) {
            if ($params['kident'] == '订单编号') {
                $query->where('order.number', 'like', "%" . $params['k'] . "%");
            }
            if ($params['kident'] == '收货人姓名') {
                $query->where('order.name', $params['k']);
            }
            if ($params['kident'] == '收货人手机') {
                $query->where('order.phone', $params['k']);
            }
            if ($params['kident'] == '用户ID') {
                $query->where('order.user_id', $params['k']);
            }
            if ($params['kident'] == '用户昵称') {
                $query->where('user.nickname', $params['k']);
            }
        }

        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $query->where('order.user_id', $params['user_id']);
        }

        if (isset($params['phone']) && !empty($params['phone'])) {
            $query->where('order.phone', $params['phone']);
        }

        if (isset($params['status']) && !empty($params['status'])) {
            $query->where('order.status', $params['status']);
        }
    }

    public function getShow($id)
    {
        $select = ['order.*', 'user.nickname as user_nickname'];
        $query = DB::table('order');
        $query->select($select);
        $query->leftJoin('user', 'order.user_id', 'user.id');
        $query->where('order.id', $id);
        $query->where('order.status', '<>', 99);
        $order = $query->first();
        if (empty($order)) return $order;

        $snaps = DB::table('order_snap')->where('order_id', $order->id)->get()->toArray();
        foreach ($snaps as $key => $value) {
            $snaps[$key]->specifications = !empty($value->specifications) ? json_decode($value->specifications, true) : [];
        }
        $order->snaps = $snaps;

        $logs = DB::table('order_log')->where('order_id', $order->id)->orderBy('created_at', 'asc')->get()->toArray();
        $order->logs = $logs;

        return $order;
    }
}
