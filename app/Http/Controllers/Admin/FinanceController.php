<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;

class FinanceController extends BaseController
{
    public function payment_log_list(Request $request)
    {
        $params = $request->all();
        $query = DB::table('payment_log');
        $query->select('payment_log.*', 'user.nickname as user_nickname');
        $query->leftJoin('user', 'user.id', 'payment_log.user_id');
        if (isset($params['status']) && $params['status'] != '') {
            $query->where('payment_log.status', $params['status']);
        }
        if (isset($params['payment_way']) && $params['payment_way'] != '') {
            $query->where('payment_log.payment_way', $params['payment_way']);
        }
        if (isset($params['k']) && !empty($params['k'])) {
            if ($params['kident'] == '用户昵称') {
                $query->where('user.nickname', $params['k']);
            }
            if ($params['kident'] == '用户ID') {
                $query->where('payment_log.user_id', $params['k']);
            }
        }
        $query->orderBy('payment_log.id', 'desc');
        $payment_logs = $query->paginate();
        foreach ($payment_logs as $key => $value) {
            $payment_logs[$key]->payment_way_show = Config('common.payment_way_array')[$value->payment_way];
        }

        return view('admin.finance.payment_log_list', compact('payment_logs'));
    }

    public function withdrawal_log_list(Request $request)
    {
        $params = $request->all();
        $query = DB::table('user_wallet_withdrawal_log');
        $query->select(['user_wallet_withdrawal_log.*', 'user.nickname as user_nickname']);
        $query->leftJoin('user', 'user.id', 'user_wallet_withdrawal_log.user_id');
        if (isset($params['k']) && !empty($params['k'])) {
            if ($params['kident'] == '用户昵称') {
                $query->where('user.nickname', $params['k']);
            }
            if ($params['kident'] == '用户ID') {
                $query->where('user.id', $params['k']);
            }
        }
        if (isset($params['status']) && $params['status'] != '') {
            $query->where('user_wallet_withdrawal_log.status', $params['status']);
        }
        $query->orderBy('user_wallet_withdrawal_log.id', 'desc');
        $withdrawal_logs = $query->paginate();
        return view('admin.finance.withdrawal_log_list', compact('withdrawal_logs'));
    }

    public function withdrawal_set(Request $request)
    {
        $withdrawal_log = DB::table('user_wallet_withdrawal_log')->where('id', $request->id)->first();
        $user = DB::table('user')->where('id', $withdrawal_log->user_id)->first();

        if ($request->isMethod('post')) {
            DB::beginTransaction();
            try {
                $params = $request->all();
                if (isset($params['status'])) $data['status'] = $params['status'];
                $data['message'] = $params['message'];
                $data['transfer_image'] = isset($params['transfer_image']) ? $params['transfer_image'] : '';
                DB::table('user_wallet_withdrawal_log')->where('id', $request->id)->update($data);

                if (isset($params['status']) && $params['status'] == 1) {
                    $data_user_wallet_log = [
                        'user_id' => $withdrawal_log->user_id,
                        'price' => $withdrawal_log->price,
                        'ident' => 'dec',
                        'description' => '提现'
                    ];
                    DB::table('user_wallet_log')->insert($data_user_wallet_log);
                }
                if (isset($params['status']) && $params['status'] == 2) {
                    DB::table('user')->where('id', $withdrawal_log->user_id)->increment('wallet', $withdrawal_log->price);
                }

                DB::commit();
                return jsonSuccess();
            } catch (\Throwable $th) {
                DB::rollBack();
                return jsonFailed($th->getMessage());
            }
        }

        return view('admin.finance.withdrawal_set', compact('withdrawal_log', 'user'));
    }
}
