<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use DB;
use App\Repositorys\AdverRepository;

class CommonController extends BaseController
{
    public function getCitys()
    {
        $citys = DB::table('city')
            ->where('level', 2)
            ->where('shortname', '<>', '')
            ->where('status', 1)
            ->get()->toArray();
        $citys = object_to_array($citys);
        $citys = arraySort($citys, 'first');
        $citys = arrayGroup($citys, 'first');
        return jsonSuccess($citys);
    }

    public function getCityList(Request $request)
    {
        $pid = $request->input('pid', 0);
        $query = DB::table('city');
        $query->where('pid', $pid);
        $citys = $query->get()->toArray();
        return jsonSuccess($citys);
    }

    // 获取所有配置
    public function getConfig()
    {
        $config = Config('common');
        // 删除部分配置数据
        unset($config['oss']);
        unset($config['sms']);
        unset($config['wxapp']['appid']);
        unset($config['wxapp']['secret']);
        unset($config['wxmp']);
        unset($config['weixinpay']);
        unset($config['alipay']);
        return jsonSuccess($config);
    }

    // 获取单条广告数据
    public function getAdver(Request $request)
    {
        $adver = app(AdverRepository::class)->getAdver($request->code);
        return jsonSuccess($adver);
    }

    // APP版本更新
    public function versionUpdate(Request $request)
    {
        $sys = [];
        $config_common = Config('common');
        if (isset($config_common[Request()->request_client])) {
            $sys = $config_common[Request()->request_client];
            if (isset($sys['version_list'][Request()->app_version]) && $sys['version_list'][Request()->app_version]) {
                $sys = array_merge($sys, $sys['version_list'][Request()->app_version]);
            }
            unset($sys['version_list']);
        }
        return jsonSuccess($sys);
    }
}
