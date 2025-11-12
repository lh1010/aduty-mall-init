<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use DB;
use Image;
use QrCode;
use App\Repositorys\AccountRepository;
use App\Repositorys\UserRepository;
use App\Repositorys\SmsRepository;
use Illuminate\Support\Facades\Cookie;

class AccountController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('CheckUserLogin')->except([
            'sendCode',
            'login',
            'login_password',
            'register',
            'wxapp_login1',
            'wxapp_login2',
            'weixinpay_notify',
            'getLoginUser',
            'wxmp_login',
            'wxmp_login_callback',
        ]);
    }

    public function login(\App\Http\Requests\Account\login $request)
    {
        $params = $request->all();
        $res = app(AccountRepository::class)->login($params);
        return $res;
    }

    public function login_password(\App\Http\Requests\Account\login_password $request)
    {
        $params = $request->all();
        $res = app(AccountRepository::class)->login_password($params);
        return $res;
    }

    public function register(\App\Http\Requests\Account\register $request)
    {
        $params = $request->all();
        $res = app(AccountRepository::class)->register($params);
        return $res;
    }

    public function sendCode(\App\Http\Requests\Account\sendCode $request)
    {
        $phone = $request->phone;
        $sms_code = DB::table('sms_code')->where(['phone' => $phone, 'is_used' => 0])->orderBy('created_at', 'desc')->first();
        if (!empty($sms_code)) {
            $date = date('Y-m-d H:i:s', strtotime('-1 minute'));
            if ($sms_code->created_at > $date) return jsonFailed('请间隔一分钟后再发送');
        }
        $params = ['phone' => $request->phone, 'code' => rand(1000, 9999)];
        $res = app(SmsRepository::class)->send($params);
        if ($res['code'] == 400) return jsonFailed('发送失败');
        $data = $params;
        DB::table('sms_code')->insert($data);
        return response()->json($res);
    }

    public function getLoginUser()
    {
        $user = getLoginUser();
        // 审核失败
        if (!empty($user) && $user->realname_auth == 3) {
            $user->realname_auth_log = DB::table('user_realname_auth_log')->where(['user_id' => $user->id])->orderBy('created_at', 'desc')->first();
        }
        return jsonSuccess($user);
    }

    public function logout(Request $request)
    {
        $user = getLoginUser();
        DB::table('user_login_log')->where(['user_id' => $user->id, 'token' => $request->user_token])->update(['status' => 0]);
        return jsonSuccess();
    }

    /**
     * 微信小程序登录
     * wx.login()
     * 获取code2seesion相关数据
     */
    public function wxapp_login1(Request $request)
    {
        $appid = Config('common.wxapp.appid');
        $secret = Config('common.wxapp.secret');
        $code = $request->code;
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $appid . '&secret=' . $secret . '&js_code=' . $code . '&grant_type=authorization_code';
        $res = curl_get($url);
        $res = json_decode($res, true);
        $unionid = isset($res['unionid']) ? $res['unionid'] : '';
        $openid = $res['openid'];
        $sessionKey = $res['session_key'];
        $code2seesion = base64_encode($unionid .'[luck]'. $openid .'[luck]'. $sessionKey);
        $return_data['code2seesion'] = $code2seesion;
        return jsonSuccess($return_data);
    }

    /**
     * 微信小程序登录
     * getPhoneNumber()
     * 授权使用手机号登录
     */
    public function wxapp_login2(Request $request)
    {
        DB::beginTransaction();
        try {
            $appid = Config('common.wxapp.appid');
            if (!$request->code2seesion) return jsonFailed('缺少code2seesion相关数据');
            $code2seesion = base64_decode($request->code2seesion);
            $array = explode('[luck]', $code2seesion);
            $unionid = $array['0'];
            $openid = $array['1'];
            $sessionKey = $array['2'];
            $iv = $request->iv;
            $encryptedData = $request->encryptedData;

            $WXBizDataCrypt = new \App\Extensions\wxApp\WXBizDataCrypt($appid, $sessionKey);
            $errCode = $WXBizDataCrypt->decryptData($encryptedData, $iv, $res);

            if ($errCode) return jsonFailed('登录失败');
            $res = json_decode($res, true);
            $phone = $res['phoneNumber'];

            $user = DB::table('user')->where('phone', $phone)->first();

            if (!empty($user)) {
                DB::table('user')->where('id', $user->id)->update(['wxapp_openid' => $openid, 'wx_unionid' => $unionid]);
                $user_id = $user->id;
            } else {
                $user_data = [];
                $user_data['wx_unionid'] = $unionid;
                $user_data['wxapp_openid'] = $openid;
                $user_data['phone'] = $phone;
                $user_data['nickname'] = 'u' . rand(100000, 999999);
                $user_data['register_client'] = '微信小程序';

                // 推荐用户
                if ($request->invite_code) {
                    $puser = DB::table('user')->where('id', $request->invite_code)->first();
                    if (!empty($puser)) $user_data['pid'] = $puser->id;
                }
                $user_id = DB::table('user')->insertGetId($user_data);

                // 填充联系方式
                DB::table('user_contact')->insert([
                    'user_id' => $user_id,
                    'phone' => $user_data['phone'],
                ]);
            }

            $return_data['user_token'] = app(AccountRepository::class)->loginSuccess($user_id);
            DB::commit();
            return jsonSuccess($return_data);
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
            return jsonFailed('登录失败，请联系管理员，谢谢~');
        }
    }

    public function qiandao(Request $request)
    {
        $gold = Config('common.user.qiandao_gold');
        DB::beginTransaction();
        try {
            $user = $request->get('user');
            $date = date('Y-m-d');
            $qiandao_log = DB::table('user_qiandao_log')->where('user_id', $user->id)->where('date', $date)->first();

            if (!empty($qiandao_log)) {
                return jsonFailed('今日已签到');
            }

            DB::table('user_qiandao_log')->insert(['user_id' => $user->id, 'date' => $date]);
            DB::table('user')->where('id', $user->id)->increment('gold', $gold);
            DB::table('user_gold_log')->insert(['user_id' => $user->id, 'gold' => $gold, 'ident' => 'inc', 'description' => '签到']);

            DB::commit();
            return jsonSuccess([], 200, '签到成功');
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed('签到失败' . $th->getMessage());
        }
    }

    public function getGoldLogsPaginate(Request $request)
    {
        $user = $request->get('user');
        $user_gold_logs = DB::table('user_gold_log')->where('user_id', $user->id)->orderBy('created_at', 'desc')->paginate();
        if ($user_gold_logs->total() > 0) {
            foreach ($user_gold_logs as $key => $value) {
                $user_gold_logs[$key]->created_at = date('Y-m-d H:i', strtotime($value->created_at));
            }
        }
        return jsonSuccess($user_gold_logs);
    }

    public function getWalletLogsPaginate(Request $request)
    {
        $user = $request->get('user');
        $user_wallet_logs = DB::table('user_wallet_log')->where('user_id', $user->id)->orderBy('id', 'desc')->paginate();
        return jsonSuccess($user_wallet_logs);
    }

    public function getWalletWithdrawalLogsPaginate(Request $request)
    {
        $user = $request->get('user');
        $withdrawal_logs = DB::table('user_wallet_withdrawal_log')->where('user_id', $user->id)->orderBy('id', 'desc')->paginate();
        foreach ($withdrawal_logs as $key => $value) {
            $withdrawal_logs[$key]->created_at = date('Y-m-d', strtotime($value->created_at));
        }
        return jsonSuccess($withdrawal_logs);
    }

    // 钱包提现
    public function walletWithdraw(\App\Http\Requests\Account\walletWithdraw $request)
    {
        DB::beginTransaction();
        try {
            $user = $request->get('user');
            //if ($user->realname_auth != 1) return jsonFailed('应监管部门要求，提现需先实名认证');
            $price = $request->price;

            // 每天可提现次数
            if (Config('common.withdrawal.today_count') > 0) {
                $start_date = date('Y-m-d') . ' 00:00:00';
                $end_date = date('Y-m-d') . ' 23:59:59';
                $count = DB::table('user_wallet_withdrawal_log')->where('user_id', $user->id)->where('created_at', '>=', $start_date)->where('created_at', '<=', $end_date)->count();
                if ($count >= Config('common.withdrawal.today_count')) return jsonFailed('每天最多可申请' . Config('common.withdrawal.today_count') . '次提现');
            }

            // 最小提现金额
            if (Config('common.withdrawal.min') > 0) {
                if (Config('common.withdrawal.min') > $price) return jsonFailed('最小提现金额' . Config('common.withdrawal.min'));
            }

            // 最大提现金额
            if (Config('common.withdrawal.max') > 0) {
                if (Config('common.withdrawal.max') < $price) return jsonFailed('最大提现金额' . Config('common.withdrawal.max'));
            }
            if ($price > $user->wallet) return jsonFailed('提现金额不能大于钱包余额');

            // 手续费
            $commission_price = 0.00;
            $final_price = $price;
            if (Config('common.withdrawal.rate') > 0) {
                if (Config('common.withdrawal.rate') >= 1) return jsonFailed('平台提现手续费比例配置错误');
                $commission_price = bcmul($price, Config('common.withdrawal.rate'), 2);
                $final_price = bcsub($price, $commission_price, 2);
            }

            $data = [
                'user_id' => $user->id,
                'price' => $price,
                'alipay_account' => $request->alipay_account,
                'alipay_name' => $request->alipay_name,
                'final_price' => $final_price,
                'commission_rate' => Config('common.withdrawal.rate'),
                'commission_price' => $commission_price
            ];
            DB::table('user_wallet_withdrawal_log')->insert($data);
            DB::table('user')->where('id', $user->id)->decrement('wallet', $price);

            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed();
        }
    }

    /**
     * 创建邀请海报图
     * @return string 图片二进制
     */
    public function createPosterImage(Request $request)
    {
        try {
            // 使用字体
            $font_path = Config('common.font_path');
            $font_path = 'D:\WWW\My2\xingqiu\public\static\default\font\msyh.ttc';

            // 跳转URL
            $user = Request()->get('user');
            $url = Config('common.app_url') . '/h5/#/pages/account/register?invite_code=' . $user->id;
            header("content-type:image/png");

            // 创建普通二维码
            $qrImg = QrCode::encoding('UTF-8')
                ->format('png')
                ->size(150)
                ->backgroundColor(255,255,255)
                ->generate($url);

            // 推广海报图
            $backgroundImg = Config('common.app_url') . Config('common.poster');

            // 初始化背景图
            $image = Image::make($backgroundImg);

            // 二维码位置
            $x = 3; $y = 295;

            // 插入二维码
            $qrcoe = Image::make($qrImg)->resize(105, 105)->encode('png', 75);
            $image->insert($qrcoe, 'center', $x, $y);
            return $image->response('png');
        } catch (\Throwable $th) {
            return jsonFailed('生成失败' . $th->getMessage());
        }
    }

    /**
     * 创建邀请海报图 小程序
     * @return string 图片二进制
     */
    public function createPosterImage_wxapp(Request $request)
    {
        // 使用字体
        $font_path = Config('common.font_path');

        $user = Request()->get('user');

        // 通过接口获取小程序二维码
        $config = Config('common.wxapp');
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $config['appid'] . '&secret=' . $config['secret'];
        $res = curl_get($url);
        $res = json_decode($res, 1);
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $res['access_token'];
        $params = [
            'scene' => '?invite_code=' . $user->id,
            'page' => 'pages/index/index',
            'is_hyaline' => true,
            'width' => 280
        ];
        $params = json_encode($params);
        $wxapp_qrcode = curl_post($url, $params);

        // 定位海报背景图
        $backgroundImg = Config('common.app_url') . Config('common.poster');

        // 初始化背景图
        $image = Image::make($backgroundImg);

        // 二维码位置
        $x = 3; $y = 295;

        // 插入小程序码
        $wxapp_qrcode = Image::make($wxapp_qrcode)->resize(105, 105)->encode('png', 75);
        $image->insert($wxapp_qrcode, 'center', $x, $y);

        header("content-type:image/png");
        return $image->response('png');
    }

    // 生成邀请链接
    public function createUrl(Request $request)
    {
        $user = Request()->get('user');
        $url = Config('common.app_url') . '/h5/#/pages/account/register?invite_code=' . $user->id;
        return jsonSuccess($url);
    }

    public function exchangeCdkey(Request $request)
    {
        if (!$request->key) return jsonFailed('卡密内容不能为空');
        $user = Request()->get('user');
        $current_date = date('Y-m-d H:i:s');
        $cdkey = DB::table('cdkey')->where('key', $request->key)->where('status', 1)->first();
        if (empty($cdkey)) return jsonFailed('卡密不存在');
        if ($cdkey->assign_user_id != 0) {
            if ($cdkey->assign_user_id != $user->id) return jsonFailed('卡密不存在');
        }
        if ($cdkey->gold <= 0) return jsonFailed('卡密无效');
        if ($cdkey->used_status != 1) return jsonFailed('该卡密已被使用');
        if (!empty($cdkey->end_date)) {
            if ($cdkey->end_date < $current_date) return jsonFailed('该卡密已过期');
        }

        DB::beginTransaction();
        try {
            DB::table('cdkey')->where('id', $cdkey->id)->update([
                'used_status' => 2,
                'used_user_id' => $user->id,
                'used_date' => $current_date
            ]);
            DB::table('user')->where('id', $user->id)->increment('gold', $cdkey->gold);
            $data_gold_log = [
                'user_id' => $user->id,
                'gold' => $cdkey->gold,
                'ident' => 'inc',
                'description' => '卡密兑换'
            ];
            DB::table('user_gold_log')->insert($data_gold_log);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function updateUser(\App\Http\Requests\Account\updateUser $request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();
            if (mb_strlen($params['nickname']) > 8) return jsonFailed('昵称长度不能超过8位');
            $user = getLoginUser();
            $data = app(UserRepository::class)->setStoreUpdateParams($params);
            DB::table('user')->where('id', $user->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function updateUserPassword(\App\Http\Requests\Account\updateUserPassword $request)
    {
        $params = $request->all();
        if ($params['password'] != $params['password_confirm']) return jsonFailed('两次密码输入不一致');

        $user = $request->get('user');
        if (empty($user->password)) {
            DB::table('user')->where('id', $user->id)->update(['password' => md5($params['password'])]);
        } else {
            if (!isset($params['password_old']) || empty($params['password_old'])) return jsonFailed('旧密码不能为空');
            if (md5($params['password_old']) != $user->password) return jsonFailed('旧密码错误');
            DB::table('user')->where('id', $user->id)->update(['password' => md5($params['password'])]);
        }

        return jsonSuccess();
    }

    public function getUserContact(Request $request)
    {
        $user = getLoginUser();
        $user_contact = DB::table('user_contact')->where('user_id', $user->id)->first();
        return jsonSuccess($user_contact);
    }

    public function updateUserContact(\App\Http\Requests\Account\updateUserContact $request)
    {
        $params = $request->all();
        if (empty($params['weixin']) && empty($params['qq']) && empty($params['phone']) && empty($params['telphone'])) return jsonFailed('最少需有一个联系方式');
        $user = $request->get('user');
        $data = [
            'user_id' => $user->id,
            'weixin' => $params['weixin'],
            'phone' => $params['phone'],
            'qq' => $params['qq'],
            'telphone' => $params['telphone'],
        ];
        if (DB::table('user_contact')->where('user_id', $user->id)->first()) {
            DB::table('user_contact')->where('user_id', $user->id)->update($data);
        } else {
            DB::table('user_contact')->insert($data);
        }
        return jsonSuccess();
    }

    // 实名认证
    public function realnameAuth(\App\Http\Requests\Account\realnameAuth $request)
    {
        $user = $request->get('user');
        $data = [
            'user_id' => $user->id,
            'realname' => $request->realname,
            'idcard' => $request->idcard,
            'idcard_img1' => fileFormat($request->idcard_img1),
            'idcard_img2' => fileFormat($request->idcard_img2),
            'status' => 1
        ];
        DB::table('user_realname_auth_log')->insert($data);
        DB::table('user')->where('id', $user->id)->update(['realname_auth' => 1]);
        return jsonSuccess();
    }

    // 实名认证重新认证
    public function realnameAuthReset(Request $request)
    {
        $user = $request->get('user');
        DB::table('user')->where('id', $user->id)->update(['realname_auth' => 0]);
        return jsonSuccess();
    }

    // 企业认证
    public function companyAuth(\App\Http\Requests\Account\companyAuth $request)
    {
        $user = $request->get('user');
        $data = [
            'user_id' => $user->id,
            'company_name' => $request->company_name,
            'social_credit_code' => $request->social_credit_code,
            'business_license' => fileFormat($request->business_license),
            'status' => 1
        ];
        DB::table('user_company_auth_log')->insert($data);
        DB::table('user')->where('id', $user->id)->update(['company_auth' => 1]);
        return jsonSuccess();
    }

    // 实名认证重新认证
    public function companyAuthReset(Request $request)
    {
        $user = $request->get('user');
        DB::table('user')->where('id', $user->id)->update(['company_auth' => 0]);
        return jsonSuccess();
    }

    // 微信公众号登录
    public function wxmp_login(Request $request)
    {
        $config = Config('common.wxmp');
        $url_ident = $request->url_ident;
        $callbackUrl = Config('common.app_url') . '/api/account/wxmp_login_callback?url_ident=' . urlencode($url_ident);
        $OAuth = new \Yurun\OAuthLogin\Weixin\OAuth2($config['appid'], $config['secret'], $callbackUrl);
        $OAuth->scope = 'snsapi_base';
        $url = $OAuth->getWeixinAuthUrl();
        session(['session_weixin_state' => $OAuth->state]);
        return redirect($url);
    }

    // 微信公众号登录
    public function wxmp_login_callback(Request $request)
    {
        $url_ident = urldecode($request->url_ident);
        $config = Config('common.wxmp');
        $OAuth = new \Yurun\OAuthLogin\Weixin\OAuth2($config['appid'], $config['secret']);
        $session_weixin_state = session('session_weixin_state');
        $accessToken = $OAuth->getAccessToken($session_weixin_state);
        $wxmp_openid = $OAuth->openid;
        $param = strstr($url_ident, '?') ? '&wxmp_openid=' . $wxmp_openid : '?wxmp_openid=' . $wxmp_openid;
        $url = Config('common.app_url') . '/h5/#' . $url_ident . $param;
        return redirect($url);
    }

    public function getTeamUsers(Request $request)
    {
        $user = Request()->get('user');
        $users = app(UserRepository::class)->getTeamUsers($user->id, $i = 1, $max_i = 1);
        return jsonSuccess($users);
    }

    // 邀请有礼 获取钱包收益
    public function getAllInviteWallet(Request $request)
    {
        $user = $request->get('user');
        $price = DB::table('user_wallet_log')->where(['user_id' => $user->id, 'description' => '邀请有礼'])->sum('price');
        return jsonSuccess($price);
    }
}
