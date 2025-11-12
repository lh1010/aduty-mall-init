<?php

namespace App\Http\Controllers\AdminApi;

use Illuminate\Http\Request;
use DB;

class CommonController extends BaseController
{
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
}
