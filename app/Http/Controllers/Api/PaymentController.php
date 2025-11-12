<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use DB;
use App\Repositorys\PaymentRepository;

class PaymentController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('CheckUserLogin')->except(
            'weixinpay_notify',
            'alipay_notify',
        );
    }

    public function pay_order(\App\Http\Requests\Payment\pay $request)
    {
        $loginUser = $request->get('user');
        $order_ids = explode(',', $request->order_ids);
        $orders = DB::table('order')->where(['user_id' => $loginUser->id, 'status' => 0])->whereIn('id', $order_ids)->get()->toArray();
        if (empty($orders)) return jsonFailed('订单不存在');

        $price = 0.00;
        foreach ($orders as $key => $value) {
            $price = bcadd($price, $value->total_price, 2);
        }
        $payment_way = $request->payment_way;
        $order_ids = implode(',', array_column($orders, 'id'));

        $data = [
            'number' => time(),
            'user_id' => $loginUser->id,
            'payment_way' => $payment_way,
            'subject' => '订单支付',
            'body' => '订单支付',
            'price' => $price,
            'order_ids' => $order_ids,
            'type' => 'order',
        ];
        $id = DB::table('payment_log')->insertGetId($data);
        $params = $data;
        $params['payment_log_id'] = $id;

        $res = $this->pay($payment_way, $loginUser, $params);
        return $res;
    }

    public function pay_wallet(\App\Http\Requests\Payment\pay_wallet $request)
    {
        $payment_way = $request->payment_way;
        $price = $request->price;
        $loginUser = $request->get('user');
        if (Config('common.max_wallet') > 0) {
            if ($loginUser->wallet >= Config('common.max_wallet')) return jsonFailed('钱包最大支持充值金额：' . Config('common.max_wallet') . '元');
        }
        $data = [
            'number' => time(),
            'user_id' => $loginUser->id,
            'payment_way' => $payment_way,
            'subject' => '钱包充值',
            'body' => '钱包充值',
            'price' => $price,
            'type' => 'wallet',
        ];
        $id = DB::table('payment_log')->insertGetId($data);
        $params = $data;
        $params['payment_log_id'] = $id;
        $res = $this->pay($payment_way, $loginUser, $params);
        return $res;
    }

    public function pay_gold(\App\Http\Requests\Payment\pay $request)
    {
        $payment_way = $request->payment_way;
        $gold = $request->gold;
        $price = getColumnArray('gold', $gold, Config('common.gold_prices'))['price'];
        $loginUser = $request->get('user');
        $data = [
            'number' => time(),
            'user_id' => $loginUser->id,
            'payment_way' => $payment_way,
            'subject' => '购买金币',
            'body' => '购买金币',
            'price' => $price,
            'gold' => $gold,
            'type' => 'gold'
        ];
        $id = DB::table('payment_log')->insertGetId($data);
        $params = $data;
        $params['payment_log_id'] = $id;
        $res = $this->pay($payment_way, $loginUser, $params);
        return $res;
    }

    /**
     * 支付入口
     * @param string $payment_way 支付方式
     * @param object $loginUser 登录用户
     * @param array $params 所需参数
     */
    private function pay($payment_way, $loginUser, $params)
    {
        $PaymentRepository = new PaymentRepository;
        switch ($payment_way) {
            case 'alipay_pc':
                //return jsonSuccess(['url' => '/api/payment/alipay_pc?payment_id=' . $params['payment_log_id']]);
                $res = $PaymentRepository->alipay_pc($params);
                break;
            case 'alipay_wap':
                $res = $PaymentRepository->alipay_wap($params);
                return $res;
                break;
            case 'alipay_jsapi':
                return $PaymentRepository->alipay_jsapi($params);
                break;
            case 'weixinpay_native':
                $qrCode = $PaymentRepository->weixinpay_native($params);
                return jsonSuccess(['qrcode' => $qrCode]);
            case 'weixinpay_h5':
                $res = $PaymentRepository->weixinpay_h5($params);
                return jsonSuccess(['url' => $res]);
                break;
            case 'weixinpay_jsapi_wxmp':
                $params['openid'] = $loginUser->wxmp_openid;
                $res = $PaymentRepository->weixinpay_jsapi_wxmp($params);
                return $res;
            case 'weixinpay_jsapi_wxapp':
                $params['openid'] = $loginUser->wxapp_openid;
                $res = $PaymentRepository->weixinpay_jsapi_wxapp($params);
                return $res;
            case 'wallet':
                $res = $PaymentRepository->pay_wallet($params, $loginUser);
                return $res;
            default:
                return jsonFailed('未知的支付方式');
        }
    }

    // 支付宝支付 直接跳转 temp
    public function alipay_pc(Request $request)
    {
        $user = $request->get('user');
        $payment_log = DB::table('payment_log')
            ->where('id', $request->payment_id)
            ->where('status', 0)
            ->where('user_id', $user->id)
            ->first();
        if (empty($payment_log)) exit('非法请求');

        $config = Config('common.alipay');
        $returnUrl = Config('common.app_url');
        switch ($params['type']) {
            case 'gold':
                $returnUrl = Config('common.app_url') . '/account/gold';
                break;
            case 'wallet':
                $returnUrl = Config('common.app_url') . '/account/wallet';
                break;
        }
        $notifyUrl = Config('common.app_url') . '/api/payment/alipay_notify';
        $outTradeNo = $payment_log->number;
        $payAmount = $payment_log->price;
        $subject = $payment_log->subject;
        $aliPay = new \App\Extensions\AliPay\pc();
        $aliPay->setAppid($config['appid']);
        $aliPay->setReturnUrl($returnUrl);
        $aliPay->setNotifyUrl($notifyUrl);
        $aliPay->setRsaPrivateKey($config['rsaPrivateKey']);
        $aliPay->setTotalFee($payAmount);
        $aliPay->setOutTradeNo($outTradeNo);
        $aliPay->setSubject($subject);
        $sHtml = $aliPay->goPay();
        return $sHtml;
    }

    // 微信支付异步通知
    public function weixinpay_notify(Request $request)
    {
        DB::beginTransaction();
        try {
            $res = false;
            $log = '';
            $config_weixinpay = Config('common.weixinpay');
            $config_wxapp = Config('common.wxapp');
            $wxPay = new \App\Extensions\WeixinPay\notify($config_weixinpay['mchid'], $config_wxapp['appid'], $config_weixinpay['apikey']);
            $res = $wxPay->notify();

            // 校验失败
            if (!$res) exit('error');

            // 支付失败
            if ($res['return_code'] != 'SUCCESS') {
                $log .= "message: return_code not SUCCESS\n";
                $log .= "res: " . json_encode($res);
                logWrite($log, 'payment/weixinpay');
                exit('error');
            }
            // 订单匹配 防止异常请求
            $payment_log = DB::table('payment_log')->where(['number' => $res['out_trade_no'], 'status' => 0])->first();
            if (empty($payment_log)) exit('error');
            // 总价是否匹配 防止异常请求
            if ($payment_log->price != ($res['total_fee'] / 100)) exit('error');

            // 校验完成 支付成功
            app(PaymentRepository::class)->paymentSuccess($payment_log->id, $res['transaction_id']);
            DB::commit();
            echo 'success';
        } catch (\Throwable $th) {
            DB::rollBack();
            $log .= "type: exceptional\n";
            $log .= "message: " . $th->getMessage()."\n";
            $log .= "params: " . json_encode($request->all());
            logWrite($log, 'payment/weixinpay');
            exit('error');
        }
    }

    // 支付宝异步通知
    public function alipay_notify(Request $request)
    {
        DB::beginTransaction();
        try {
            $res = false;
            $log = '';
            $config = Config('common.alipay');

            if (!$request->isMethod('post') || !$request->sign) exit('error');

            $aliPay = new \App\Extensions\AliPay\notify();
            $aliPay->setAlipayPublicKey($config['alipayPublicKey']);
            $res = $aliPay->rsaCheck($request->all(), $request->sign_type);

            // 验证 asin 失败
            if (!$res) exit('error');

            // 支付失败
            if ($request->trade_status != 'TRADE_SUCCESS') {
                $log .= "message: trade_status not TRADE_SUCCESS\n";
                $log .= "res: " . json_encode($res);
                logs($log, 'payment/alipay');
                exit('error');
            }

            // 订单匹配 防止异常请求
            $payment_log = DB::table('payment_log')->where(['number' => $request->out_trade_no, 'status' => 0])->first();
            if (empty($payment_log)) exit('error');

            // 总价是否匹配 防止异常请求
            if ($payment_log->price != $request->total_amount) exit('error');

            // 校验完成 支付成功
            app(PaymentRepository::class)->paymentSuccess($payment_log->id, $request->trade_no);
            DB::commit();
            echo 'success';
        } catch (\Throwable $th) {
            DB::rollBack();
            $log .= "type: exceptional\n";
            $log .= "message: " . $th->getMessage()."\n";
            $log .= "params: " . json_encode($request->all());
            logWrite($log, 'payment/alipay');
            exit('error');
        }
    }

    public function pay_vip(Request $request)
    {
        $user = $request->get('user');
        $month = $request->month;
        $gold = getColumnArray('month', $month, Config('common.vip_prices'))['gold'];
        if ($user->gold < $gold) return jsonFailed('金币不足', 4001);

        DB::beginTransaction();
        try {
            $now_time = time();
            $data_user_member['user_id'] = $user->id;
            $data_user_member['start_date'] = date('Y-m-d H:i:s', time());
            $user_member = DB::table('user_member')->where('user_id', $user->id)->orderBy('end_date', 'desc')->first();
            if (!empty($user_member) && strtotime($user_member->end_date) > $now_time) {
                $data_user_member['start_date'] = $user_member->end_date;
            }
            $data_user_member['end_date'] = date('Y-m-d H:i:s', strtotime("+". $month ." month", strtotime($data_user_member['start_date'])));
            DB::table('user_member')->insert($data_user_member);

            DB::table('user')->where('id', $user->id)->decrement('gold', $gold);
            $data_gold_log = [
                'user_id' => $user->id,
                'gold' => $gold,
                'ident' => 'dec',
                'description' => '购买会员'
            ];
            DB::table('user_gold_log')->insert($data_gold_log);
            DB::commit();
            return jsonSuccess($data = '', $code = 200, $message = '开通成功');
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed();
        }
    }
}
