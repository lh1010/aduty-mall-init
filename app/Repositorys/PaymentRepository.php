<?php

namespace App\Repositorys;

use DB;

class PaymentRepository
{
    /**
     * @param int $payment_log_id
     * @param int $trade_no 三方平台订单号
     */
    public function paymentSuccess($payment_log_id, $trade_no = '')
    {
        $payment_log = DB::table('payment_log')->where('id', $payment_log_id)->where('status', 0)->first();
        if (empty($payment_log)) return arrayFailed('支付记录不存在');

        $data_payment_log = ['trade_no' => $trade_no, 'status' => 1];
        DB::table('payment_log')->where('id', $payment_log->id)->update($data_payment_log);

        if ($payment_log->type == 'wallet') {
            DB::table('user')->where('id', $payment_log->user_id)->increment('wallet', $payment_log->price);
            $data_wallet_log = [
                'user_id' => $payment_log->user_id,
                'price' => $payment_log->price,
                'ident' => 'inc',
                'description' => '充值'
            ];
            DB::table('user_wallet_log')->insert($data_wallet_log);
        }

        if ($payment_log->type == 'gold') {
            DB::table('user')->where('id', $payment_log->user_id)->increment('gold', $payment_log->gold);
            $data_gold_log = [
                'user_id' => $payment_log->user_id,
                'gold' => $payment_log->gold,
                'ident' => 'inc',
                'description' => '充值'
            ];
            DB::table('user_gold_log')->insert($data_gold_log);
            $user = DB::table('user')->where('id', $payment_log->user_id)->first();
            $this->pay_fx(
                $payment_log->user_id,
                $payment_log->price,
                $i = 1,
                $max_i = 1,
                [
                    'type' => '分销收益',
                    'description' => '团队成员【' . $user->nickname . '】购买金币的收益'
                ]
            );
        }

        if ($payment_log->type == 'order') {
            $order_ids = explode(',', $payment_log->order_ids);
            DB::table('order')->whereIn('id', $order_ids)->update(['status' => 10]);
            $data_order_log = [];
            foreach ($order_ids as $key => $value) {
                $data_order_log[$key]['order_id'] = $value;
                $data_order_log[$key]['content'] = '买家已付款';
            }
            DB::table('order_log')->insert($data_order_log);
        }

        return arraySuccess();
    }

    // 钱包支付
    public function pay_wallet($params, $user)
    {
        if ($user->wallet < $params['price']) return arrayFailed('钱包余额不足');
        DB::table('user')->where('id', $user->id)->decrement('wallet', $params['price']);
        $data_wallet_log = [
            'user_id' => $user->id,
            'price' => $params['price'],
            'ident' => 'dec',
            'description' => $params['body']
        ];
        DB::table('user_wallet_log')->insert($data_wallet_log);
        $this->paymentSuccess($params['payment_log_id']);
        return arraySuccess();
    }

    /**
     * 微信支付 扫码支付
     * 使用小程序配置
     * @return string 二维码
     */
    public function weixinpay_native($params)
    {
        $config_weixinpay = Config('common.weixinpay');
        $config_wxapp = Config('common.wxapp');
        $wxPay = new \App\Extensions\WeixinPay\native($config_weixinpay['mchid'], $config_wxapp['appid'], $config_weixinpay['apikey']);
        $outTradeNo = $params['number'];
        $payAmount = $params['price'];
        $orderName = $params['subject'];
        $notifyUrl = Config('common.app_url') . '/api/payment/weixinpay_notify';
        $payTime = time();
        $array = $wxPay->createJsBizPackage($payAmount, $outTradeNo, $orderName, $notifyUrl, $payTime);
        $qrCode = 'https://api.pwmqr.com/qrcode/create?url=' . $array['code_url'];
        return $qrCode;
    }

    /**
     * 微信支付 jsapi 公众号
     * @param $params['openid'] 公众号openid
     * @return array jsApiParams
     */
    public function weixinpay_jsapi_wxmp($params)
    {
        $config_weixinpay = Config('common.weixinpay');
        $config_wxmp = Config('common.wxmp');
        $wxPay = new \App\Extensions\WeixinPay\jsapi($config_weixinpay['mchid'], $config_wxmp['appid'], $config_wxmp['secret'], $config_weixinpay['apikey']);

        $outTradeNo = $params['number'];
        $payAmount = $params['price'];
        $orderName = $params['subject'];
        $notifyUrl = Config('common.app_url') . '/api/payment/weixinpay_notify';
        $payTime = time();
        $openId = $params['openid'];

        $jsApiParams = $wxPay->createJsBizPackage($openId, $payAmount, $outTradeNo, $orderName, $notifyUrl, $payTime);
        return arraySuccess(['jsApiParams' => $jsApiParams]);
    }

    /**
     * 微信支付 jsapi 小程序
     * @param $params['openid'] 小程序openid
     * @return array jsApiParams
     */
    public function weixinpay_jsapi_wxapp($params)
    {
        $config_weixinpay = Config('common.weixinpay');
        $config_wxapp = Config('common.wxapp');
        $wxPay = new \App\Extensions\WeixinPay\jsapi($config_weixinpay['mchid'], $config_wxapp['appid'], $config_wxapp['secret'], $config_weixinpay['apikey']);

        $outTradeNo = $params['number'];
        $payAmount = $params['price'];
        $orderName = $params['subject'];
        $notifyUrl = Config('common.app_url') . '/api/payment/weixinpay_notify';
        $payTime = time();
        $openId = $params['openid'];

        $jsApiParams = $wxPay->createJsBizPackage($openId, $payAmount, $outTradeNo, $orderName, $notifyUrl, $payTime);
        return arraySuccess(['jsApiParams' => $jsApiParams]);
    }

    /**
     * 微信扫码支付 H5支付
     * @return string 跳转url
     */
    public function weixinpay_h5($params)
    {
        $config_weixinpay = Config('common.weixinpay');
        $config_wxapp = Config('common.wxapp');

        $returnUrl = Config('common.app_url');
        switch ($params['type']) {
            case 'gold':
                $returnUrl = Config('common.app_url') . '/h5/#/pages/account/gold';
                break;
            case 'wallet':
                $returnUrl = Config('common.app_url') . '/h5/#/pages/account/wallet';
                break;
            case 'order_danbao':
                $returnUrl = Config('common.app_url') . '/h5/#/pages/danbao/show?id=' . $params['order_danbao_id'];
                break;
        }
        $notifyUrl = Config('common.app_url') . '/api/payment/weixinpay_notify';
        $outTradeNo = $params['number'];
        $payAmount = $params['price'];
        $orderName = $params['subject'];
        $wapUrl = Config('common.app_name');
        $wapName = Config('common.app_url');

        $wxPay = new \App\Extensions\WeixinPay\h5($config_weixinpay['mchid'], $config_wxapp['appid'], $config_weixinpay['apikey']);
        $wxPay->setTotalFee($payAmount);
        $wxPay->setOutTradeNo($outTradeNo);
        $wxPay->setOrderName($orderName);
        $wxPay->setNotifyUrl($notifyUrl);
        $wxPay->setReturnUrl($returnUrl);
        $wxPay->setWapUrl($wapUrl);
        $wxPay->setWapName($wapName);

        $url = $wxPay->createJsBizPackage($payAmount, $outTradeNo, $orderName, $notifyUrl);
        return $url;
    }

    /**
     * alipay pc
     * @return string form表单
     */
    public function alipay_pc($params)
    {
        $config = Config('common.alipay');
        $returnUrl = Config('common.app_url');
        switch ($params['type']) {
            case 'gold':
                $returnUrl = Config('common.app_url') . '/account/gold';
                break;
            case 'wallet':
                $returnUrl = Config('common.app_url') . '/account/wallet';
                break;
            case 'order_danbao':
                $returnUrl = Config('common.app_url') . '/account/danbao';
                break;
        }
        $notifyUrl = Config('common.app_url') . '/api/payment/alipay_notify';
        $outTradeNo = $params['number'];
        $payAmount = $params['price'];
        $subject = $params['subject'];
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

    /**
     * alipay wap
     * @return string form表单
     */
    public function alipay_wap($params)
    {
        $config = Config('common.alipay');
        $returnUrl = Config('common.app_url');
        switch ($params['type']) {
            case 'gold':
                $returnUrl = Config('common.app_url') . '/h5/#/pages/account/gold';
                break;
            case 'wallet':
                $returnUrl = Config('common.app_url') . '/h5/#/pages/account/wallet';
                break;
            case 'order_danbao':
                $returnUrl = Config('common.app_url') . '/h5/#/pages/danbao/show?id=' . $params['order_danbao_id'];
                break;
        }
        $notifyUrl = Config('common.app_url') . '/api/payment/alipay_notify';
        $outTradeNo = $params['number'];
        $payAmount = $params['price'];
        $orderName = $params['subject'];

        $aliPay = new \App\Extensions\AliPay\wap();
        $aliPay->setAppid($config['appid']);
        $aliPay->setReturnUrl($returnUrl);
        $aliPay->setNotifyUrl($notifyUrl);
        $aliPay->setRsaPrivateKey($config['rsaPrivateKey']);
        $aliPay->setTotalFee($payAmount);
        $aliPay->setOutTradeNo($outTradeNo);
        $aliPay->setOrderName($orderName);
        $sHtml = $aliPay->doPay();
        return arraySuccess($sHtml);
    }

    /**
     * alipay jsapi APP支付
     * @return array jsApiParams
     */
    public function alipay_jsapi($params)
    {
        $config = Config('common.alipay');
        $returnUrl = Config('common.app_url');
        switch ($params['type']) {
            case 'gold':
                $returnUrl = Config('common.app_url') . '/h5/#/pages/account/gold';
                break;
            case 'wallet':
                $returnUrl = Config('common.app_url') . '/h5/#/pages/account/wallet';
                break;
            case 'order_danbao':
                $returnUrl = Config('common.app_url') . '/h5/#/pages/danbao/show?id=' . $params['order_danbao_id'];
                break;
        }
        $notifyUrl = Config('common.app_url') . '/api/payment/alipay_notify';
        $outTradeNo = strval($params['number']);
        $payAmount = $params['price'];
        $orderName = $params['subject'];

        $aliPay = new \App\Extensions\AliPay\jsapi();
        $aliPay->setAppid($config['appid']);
        $aliPay->setReturnUrl($returnUrl);
        $aliPay->setNotifyUrl($notifyUrl);
        $aliPay->setRsaPrivateKey($config['rsaPrivateKey']);
        $aliPay->setTotalFee($payAmount);
        $aliPay->setOutTradeNo($outTradeNo);
        $aliPay->setOrderName($orderName);
        $orderStr = $aliPay->getOrderStr();
        return arraySuccess($orderStr);
    }

    public function getWalletLogs($params = [], $type = 'paginate', $limit = 15)
    {
        $select = [
            'user_wallet_log.*',
        ];
        $query = DB::table('user_wallet_log');
        $this->setParams_getWalletLogs($query, $params, $limit);
        if ($type == 'paginate') {
            $logs = $query->paginate($limit);
        } else {
            if ($limit >= 0 ) $query->limit($limit);
            $logs = $query->get()->toArray();
        }
        return $logs;
    }

    public function setParams_getWalletLogs($query, $params = [])
    {
        $query->orderBy('user_wallet_log.id', 'desc');

        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $query->where('user_wallet_log.user_id', $params['user_id']);
        }

        if (isset($params['ident']) && !empty($params['ident'])) {
            $query->where('user_wallet_log.ident', $params['ident']);
        }

        if (isset($params['type']) && !empty($params['type'])) {
            $query->where('user_wallet_log.type', $params['type']);
        }

        if (isset($params['types']) && !empty($params['types'])) {
            $query->whereIn('user_wallet_log.type', $params['types']);
        }
    }

    /**
     * 消费收益 团队分销
     * @param int $user_id
     * @param float $pay_price 消费金额
     * @param int $i 当前级
     * @param int $max_i 最大级 默认1级分销
     * @param string $params['type']
     * @param string $params['description']
     */
    public function pay_fx($user_id, $pay_price, $i = 1, $max_i = 1, $params = [])
    {
        if (Config('common.team.rate') <= 0) return false;

        if ($max_i > 0 && $i > $max_i) return false;

        // 消费用户
        $user = DB::table('user')->where('id', $user_id)->first();
        if (empty($user) || $user->pid == 0) return false;

        // 上级用户
        $puser = DB::table('user')->where('id', $user->pid)->first();
        if (empty($puser)) return false;

        // 确定收益
        $price = bcmul($pay_price, Config('common.team.rate'), 2);

        if ($price > 0) {
            DB::table('user')->where('id', $puser->id)->increment('wallet', $price);
            DB::table('user_wallet_log')->insert([
                'user_id' => $puser->id,
                'price' => $price,
                'description' => $params['description']
            ]);
        }

        $i++;
        $this->pay_fx($puser->id, $pay_price, $i, $max_i, $params);
    }
}
