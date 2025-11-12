<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use DB;

class TestController extends BaseController
{
    /**
     * Alipay
     * 单笔转账到支付宝账户
     * https://opendocs.alipay.com/open-v3/08e7ef12_alipay.fund.trans.uni.transfer
     */
    public function alipay_transfer(Request $request)
    {
        $config = Config('common.alipay');

        // 设置alipayConfig参数
        $alipayConfig = new \Alipay\OpenAPISDK\Util\Model\AlipayConfig();
        $alipayConfig->setServerUrl('https://openapi.alipay.com');
        $alipayConfig->setAppId($config['appid']);
        $alipayConfig->setPrivateKey($config['rsaPrivateKey']);
        // 应用公钥证书文件路径 如：/foo/appCertPublicKey_2019051064521003.crt
        $appCertPath = '/www/wwwroot/adutycmf/public/alipay/appCertPublicKey_2021003118692091.crt';
        // $alipayConfig->setAppCertContent(file_get_contents($appCertPath));
        $alipayConfig->setAppCertPath($appCertPath);
        // 支付宝公钥证书文件路径 如：/foo/alipayCertPublicKey_RSA2.crt
        $alipayPublicCertPath = '/www/wwwroot/adutycmf/public/alipay/alipayCertPublicKey_RSA2.crt';
        // $alipayConfig->setAlipayPublicCertContent(file_get_contents($alipayPublicCertPath));
        $alipayConfig->setAlipayPublicCertPath($alipayPublicCertPath);
        // 支付宝根证书文件路径 如：/foo/alipayRootCert.crt
        $rootCertPath = '/www/wwwroot/adutycmf/public/alipay/alipayRootCert.crt';
        // $alipayConfig->setRootCertContent(file_get_contents($rootCertPath));
        $alipayConfig->setRootCertPath($rootCertPath);

        // 初始化SDK
        $alipayConfigUtil = new \Alipay\OpenAPISDK\Util\AlipayConfigUtil($alipayConfig);

        // 构造请求参数以调用接口
        $apiInstance = new \Alipay\OpenAPISDK\Api\AlipayFundTransUniApi();

        // 设置AlipayConfigUtil
        $apiInstance->setAlipayConfigUtil($alipayConfigUtil);
        $data = new \Alipay\OpenAPISDK\Model\AlipayFundTransUniTransferModel();

        // 设置商家侧唯一订单号
        $data->setOutBizNo(time());

        // 设置订单总金额
        $data->setTransAmount("1.00");

        // 设置描述特定的业务场景
        $data->setBizScene("DIRECT_TRANSFER");

        // 设置业务产品码
        $data->setProductCode("TRANS_ACCOUNT_NO_PWD");

        // 设置转账业务的标题
        $data->setOrderTitle("提现打款");

        // 设置收款方信息
        $payeeInfo = new \Alipay\OpenAPISDK\Model\Participant();
        $payeeInfo->setIdentity("17311111111");
        $payeeInfo->setName("张三");
        $payeeInfo->setIdentityType("ALIPAY_LOGON_ID");
        $data->setPayeeInfo($payeeInfo);

        try {
            $result = $apiInstance->transfer($data);
            dd($result);
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    /**
     * Alipay
     * 单笔转账到支付宝账户
     * 旧版
     */
    public function alipay_transfers(Request $request)
    {
        $config = Config('common.alipay');
        $aliPay = new \App\Extensions\AliPay\transfers();
        $aliPay->setAppid($config['appid']);
        $aliPay->setRsaPrivateKey($config['rsaPrivateKey']);
        $payAmount = 1.00;
        $outTradeNo = time();
        $remark = '提现打款';
        $account = 'alipay_account';
        $realName = 'realName';
        $res = $aliPay->doPay($payAmount, $outTradeNo, $account, $realName, $remark);
        $res = $res['alipay_fund_trans_toaccount_transfer_response'];
        if ($res['code'] && $res['code'] == '10000') {
            // 支付宝转账日志
            // ......
        } else {
            return jsonFailed('支付宝接口返回：' . $res['msg'] . ' : ' . $res['sub_msg']);
        }
    }

    /**
     * Alipay
     * 单笔转账接口
     * 转账到支付宝账户
     * https://opendocs.alipay.com/open/62987723_alipay.fund.trans.uni.transfer
     */
    public function alipay_transfers_new(Request $request)
    {
        $config = Config('common.alipay');
        $aliPay = new \App\Extensions\AliPay\transfers_new();
        $aliPay->setAppid($config['appid']);
        $aliPay->setRsaPrivateKey($config['rsaPrivateKey']);

        $payAmount = 1.00;
        $outTradeNo = time();
        $remark = '提现打款';
        $account = 'alipay_account';
        $realName = 'realName';
        $res = $aliPay->doTransfer($payAmount, $outTradeNo, $account, $realName, $remark);
        dd($res);
        $res = $res['alipay_fund_trans_toaccount_transfer_response'];
        if ($res['code'] && $res['code'] == '10000') {
            // 支付宝转账日志
            // ......
        } else {
            return jsonFailed('支付宝接口返回：' . $res['msg'] . ' : ' . $res['sub_msg']);
        }
    }
}
