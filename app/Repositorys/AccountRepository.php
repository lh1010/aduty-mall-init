<?php

namespace App\Repositorys;

use DB;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Cookie;

class AccountRepository
{
    public function register($params)
    {
        if ($params['password'] != $params['password_confirm']) return jsonFailed('两次密码输入不一致');
        $sms_code = DB::table('sms_code')->where(['phone' => $params['phone'], 'code' => $params['code'], 'is_used' => 0])->orderBy('created_at', 'desc')->first();

        // 超级验证码
        if ($params['code'] != Config('common.sms_code')) {
            if (empty($sms_code)) return jsonFailed('短信验证码错误');
            $date = date('Y-m-d H:i:s', strtotime('-5 minute'));
            if ($sms_code->created_at < $date) return arrayFailed('短信验证码已过期');
        }

        $user = DB::table('user')->where('phone', $params['phone'])->first();
        if (!empty($user) && $user->status == 0) return jsonFailed('该手机号已被禁用，请联系客服');
        if (!empty($user)) return jsonFailed('该手机号已注册，请直接登录');
        $params['register_client'] = isset($params['request_client']) ? $params['request_client'] : '电脑网站';

        DB::beginTransaction();
        try {
            $user_data = [
                'phone' => $params['phone'],
                'nickname' => $params['nickname'],
                'password' => md5($params['password']),
                'register_client' => $params['register_client']
            ];

            // 推荐用户
            if (isset($params['invite_code']) && !empty($params['invite_code'])) {
                $puser = DB::table('user')->where('id', $params['invite_code'])->first();
                if (!empty($puser)) $user_data['pid'] = $puser->id;
            }
            $user_id = DB::table('user')->insertGetId($user_data);

            // 填充联系方式
            DB::table('user_contact')->insert(['user_id' => $user_id, 'phone' => $params['phone']]);

            // 微信内打开h5 更新用户wxmp_openid
            if (isset($params['type']) && $params['type'] == 'wxmp') {
                if (!isset($params['wxmp_openid']) || empty($params['wxmp_openid'])) {
                    DB::rollBack();
                    return jsonFailed('微信授权已失效，请刷新当前页面');
                }
                DB::table('user')->where('id', $user_id)->update(['wxmp_openid' => $params['wxmp_openid']]);
            }

            // 小程序客户端
            if (isset($params['request_client']) && $params['request_client'] == 'wxapp') {
                if (!isset($params['code2seesion']) || empty($params['code2seesion'])) {
                    DB::rollBack();
                    return jsonFailed('微信授权已失效，请刷新当前页面');
                }
                $code2seesion = base64_decode($params['code2seesion']);
                $array = explode('[luck]', $code2seesion);
                $openid = $array['1'];
                DB::table('user')->where('id', $user_id)->update(['wxapp_openid' => $openid]);
            }

            $token = $this->loginSuccess($user_id);
            Cookie::queue('user_token', $token, Config('common.user_hold_login_time'));

            if (!empty($sms_code)) DB::table('sms_code')->where('id', $sms_code->id)->update(['is_used' => 1]);
            DB::commit();
            return jsonSuccess(['user_token' => $token]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    /**
     * 登录 手机验证码登录
     * @param string $params['phone']
     * @param int $params['code']
     */
    public function login($params)
    {
        $sms_code = DB::table('sms_code')->where(['phone' => $params['phone'], 'code' => $params['code'], 'is_used' => 0])->orderBy('created_at', 'desc')->first();

        // 超级验证码
        if ($params['code'] != Config('common.sms_code')) {
            if (empty($sms_code)) return jsonFailed('短信验证码错误');
            $date = date('Y-m-d H:i:s', strtotime('-5 minute'));
            if ($sms_code->created_at < $date) return arrayFailed('短信验证码已过期');
        }

        DB::beginTransaction();
        try {
            $user = DB::table('user')->where('phone', $params['phone'])->first();
            if (!empty($user)) {
                if ($user->status == 0) return jsonFailed('该手机号已被禁用，请联系客服');
                $user_id = $user->id;
            } else {
                // 验证码登录 新用户
                $user_data = ['phone' => $params['phone'], 'nickname' => 'u' . rand(100000, 999999)];

                // 注册客户端
                $user_data['register_client'] = isset($params['request_client']) ? $params['request_client'] : '电脑网站';

                // 推荐用户
                if (isset($params['invite_code']) && !empty($params['invite_code'])) {
                    $puser = DB::table('user')->where('id', $params['invite_code'])->first();
                    if (!empty($puser)) $user_data['pid'] = $puser->id;
                }
                $user_id = DB::table('user')->insertGetId($user_data);

                // 填充联系方式
                DB::table('user_contact')->insert(['user_id' => $user_id, 'phone' => $params['phone']]);
            }

            // 微信内打开h5 更新用户wxmp_openid
            if (isset($params['type']) && $params['type'] == 'wxmp') {
                if (!isset($params['wxmp_openid']) || empty($params['wxmp_openid'])) {
                    DB::rollBack();
                    return jsonFailed('微信授权已失效，请刷新当前页面');
                }
                DB::table('user')->where('id', $user_id)->update(['wxmp_openid' => $params['wxmp_openid']]);
            }

            // 小程序客户端
            if (isset($params['request_client']) && $params['request_client'] == 'wxapp') {
                if (!isset($params['code2seesion']) || empty($params['code2seesion'])) {
                    DB::rollBack();
                    return jsonFailed('微信授权已失效，请刷新当前页面');
                }
                $code2seesion = base64_decode($params['code2seesion']);
                $array = explode('[luck]', $code2seesion);
                $openid = $array['1'];
                DB::table('user')->where('id', $user_id)->update(['wxapp_openid' => $openid]);
            }

            $token = $this->loginSuccess($user_id);
            Cookie::queue('user_token', $token, Config('common.user_hold_login_time'));

            if (!empty($sms_code)) DB::table('sms_code')->where('id', $sms_code->id)->update(['is_used' => 1]);
            DB::commit();
            return jsonSuccess(['user_token' => $token]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    /**
     * 登录 密码登录
     * @param string $params['phone']
     * @param int $params['password']
     */
    public function login_password($params)
    {
        if (!DB::table('user')->where('phone', $params['phone'])->first()) return jsonFailed('该手机号未注册');
        if (!$user = DB::table('user')->where(['phone' => $params['phone'], 'password' => md5($params['password'])])->first()) return jsonFailed('密码错误');
        if ($user->status == 0) return jsonFailed('该用户已关闭，请联系客服');

        // 微信内打开h5 更新用户wxmp_openid
        if (isset($params['type']) && $params['type'] == 'wxmp') {
            if (!isset($params['wxmp_openid']) || empty($params['wxmp_openid'])) return jsonFailed('微信授权已失效，请刷新当前页面');
            DB::table('user')->where('id', $user->id)->update(['wxmp_openid' => $params['wxmp_openid']]);
        }

        // 小程序客户端
        if (isset($params['request_client']) && $params['request_client'] == 'wxapp') {
            if (!isset($params['code2seesion']) || empty($params['code2seesion'])) return jsonFailed('微信授权已失效，请刷新当前页面');
            $code2seesion = base64_decode($params['code2seesion']);
            $array = explode('[luck]', $code2seesion);
            $openid = $array['1'];
            DB::table('user')->where('id', $user->id)->update(['wxapp_openid' => $openid]);
        }

        $token = $this->loginSuccess($user->id);
        Cookie::queue('user_token', $token, Config('common.user_hold_login_time'));
        return jsonSuccess(['user_token' => $token]);
    }

    public function loginSuccess($user_id)
    {
        $data['user_id'] = $user_id;
        $data['ip'] = Request()->ip();
        $data['browser'] = app(Agent::class)->browser();
        $data['browser_version'] = app(Agent::class)->version($data['browser']);
        $data['token'] = md5($user_id . time());
        DB::table('user_login_log')->insertGetId($data);
        return $data['token'];
    }

    /**
     * 邀请有礼 团队分销
     * @param int $user_id 注册用户ID
     * @param int $i 当前级
     * @param int $max_i 最大级
     */
    public function invite_fx($user_id, $i = 1, $max_i = 1)
    {
        $user = DB::table('user')->where('id', $user_id)->first();
        if (empty($user) || $user->pid == 0) return false;
        if ($i == 1) $this->register_user = $user;
        if ($max_i > 0 && $i > $max_i) return false;

        // 上级用户
        $puser = DB::table('user')->where('id', $user->pid)->first();
        if (empty($puser)) return false;

        // 邀请有礼 赠送上级用户金币
        DB::table('user')->where('id', $puser->id)->increment('gold', Config('common.user.invite_user_gold'));
        DB::table('user_gold_log')->insert([
            'user_id' => $puser->id,
            'gold' => Config('common.invite_user_gold'),
            'ident' => 'inc',
            'description' => '邀请有礼'
        ]);

        $i++;
        $this->invite_fx($puser->id, $i, $max_i);
    }
}
