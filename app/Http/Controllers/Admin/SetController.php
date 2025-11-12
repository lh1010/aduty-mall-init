<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;

class SetController extends BaseController
{
    // 系统配置
    public function system(Request $request)
    {
        if ($request->isMethod('post')) {
            $params = $request->all();
            unset($params['_token']);
            $common = Config('common');
            $common = array_merge($common, $params);
            writeConfigFile($common, 'common.php');
            return jsonSuccess();
        }
        return view('admin.set.system');
    }

    // 电脑网站配置
    public function client_pc(Request $request)
    {
        if ($request->isMethod('post')) {
            $params = $request->all();
            unset($params['_token']);
            $common = Config('common');
            $common = array_merge($common, $params);
            writeConfigFile($common, 'common.php');
            return jsonSuccess();
        }
        return view('admin.set.client_pc');
    }

    // 客户端 微信小程序 配置
    public function client_wxapp(Request $request)
    {
        if ($request->isMethod('post')) {
            $params = $request->all();
            unset($params['_token']);
            $common = Config('common');
            $common = array_merge($common, $params);
            writeConfigFile($common, 'common.php');
            return jsonSuccess();
        }
        return view('admin.set.client_wxapp');
    }

    // 客户端 微信公众号 配置
    public function client_wxmp(Request $request)
    {
        if ($request->isMethod('post')) {
            $params = $request->all();
            unset($params['_token']);
            $common = Config('common');
            $common = array_merge($common, $params);
            writeConfigFile($common, 'common.php');
            return jsonSuccess();
        }
        return view('admin.set.client_wxmp');
    }

    // 短信配置
    public function sms(Request $request)
    {
        if ($request->isMethod('post')) {
            $params = $request->all();
            unset($params['_token']);

            $keys = array_keys($params['sms']);
            $sms = Config('common.sms');
            foreach ($sms as $key => $value) {
                if (in_array($key, $keys)) {
                    $sms[$key] = $params['sms'][$key];
                }
            }
            $params['sms'] = $sms;

            $common = Config('common');
            $common = array_merge($common, $params);
            writeConfigFile($common, 'common.php');
            return jsonSuccess();
        }
        return view('admin.set.sms');
    }

    // 短信模板
    public function sms_template(Request $request)
    {
        return view('admin.set.sms_template');
    }

    // 短信模板code
    public function set_sms_template_code(Request $request)
    {
        if ($request->isMethod('post')) {
            $params = $request->all();
            unset($params['_token']);

            $keys = array_keys($params['sms']);
            $sms = Config('common.sms');
            foreach ($sms as $key => $value) {
                if (in_array($key, $keys)) {
                    $sms[$key] = $params['sms'][$key];
                }
            }
            $params['sms'] = $sms;

            $common = Config('common');
            $common = array_merge($common, $params);
            writeConfigFile($common, 'common.php');
            return jsonSuccess();
        }
        $res = Config('common.sms.template')[$request->id];
        return view('admin.set.set_sms_template_code', compact('res'));
    }

    // 支付配置 微信支付
    public function payment_weixinpay(Request $request)
    {
        if ($request->isMethod('post')) {
            $params = $request->all();
            unset($params['_token']);
            $common = Config('common');
            $common = array_merge($common, $params);
            writeConfigFile($common, 'common.php');
            return jsonSuccess();
        }
        return view('admin.set.payment_weixinpay');
    }

    // 支付配置 支付宝
    public function payment_alipay(Request $request)
    {
        if ($request->isMethod('post')) {
            $params = $request->all();
            unset($params['_token']);
            $common = Config('common');
            $common = array_merge($common, $params);
            writeConfigFile($common, 'common.php');
            return jsonSuccess();
        }
        return view('admin.set.payment_alipay');
    }
}
