<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use DB;
use App\Repositorys\AddressRepository;

class AddressController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('CheckUserLogin');
    }

    public function getShow(Request $request)
    {
        $user = $request->get('user');
        $address = DB::table('user_address')->where(['id' => $request->id, 'user_id' => $user->id])->first();
        return jsonSuccess($address);
    }

    public function getList(Request $request)
    {
        $user = $request->get('user');
        $addresses = DB::table('user_address')->where('user_id', $user->id)->orderBy('default', 'desc')->orderBy('id', 'desc')->get();
        return jsonSuccess($addresses);
    }

    public function store(\App\Http\Requests\Address\store $request)
    {
        $user = $request->get('user');
        $addresses = DB::table('user_address')->where(['user_id' => $user->id])->get()->toArray();
        if (count($addresses) >= 10) return jsonFailed('最多只能保存10条地址');
        $params = $request->all();
        // 拆分区划数组
        $region = $params['region'] ?? [];
        if (!empty($region)) {
            $params['province_id'] = $region[0];
            $params['city_id'] = $region[1];
            $params['district_id'] = $region[2];
        }
        if (empty($params['district_id'])) return jsonFailed('请选择所在区域');
        $params['user_id'] = $user->id;
        $regionIds = [$params['province_id'], $params['city_id'], $params['district_id']];
        $cities = DB::table('city')->whereIn('id', $regionIds)->pluck('name', 'id')->toArray();
        $params['province_name'] = $cities[$params['province_id']] ?? '';
        $params['city_name'] = $cities[$params['city_id']] ?? '';
        $params['district_name'] = $cities[$params['district_id']] ?? '';

        $data = app(AddressRepository::class)->setStoreUpdateParams($params);
        if ($data['default'] == 1) {
            DB::table('user_address')->where('user_id', $user->id)->update(['default' => 0]);
        } else {
            $defaults = array_column($addresses, 'default');
            if (empty($addresses) || max($defaults) < 1) $data['default'] = 1;
        }
        DB::table('user_address')->insert($data);
        return jsonSuccess();
    }

    public function update(\App\Http\Requests\Address\update $request)
    {
        $user = $request->get('user');
        $address = DB::table('user_address')->where(['id' => $request->id, 'user_id' => $user->id])->first();
        if (empty($address)) return jsonFailed('该地址不存在');
        $params = $request->all();
        $data = app(AddressRepository::class)->setStoreUpdateParams($params);
        if ($data['default'] == 1) {
            DB::table('user_address')->where('user_id', $user->id)->update(['default' => 0]);
        }
        DB::table('user_address')->where('id', $address->id)->update($data);
        return jsonSuccess();
    }

    public function delete(Request $request)
    {
        $user = $request->get('user');
        $address = DB::table('user_address')->where(['id' => $request->id, 'user_id' => $user->id])->first();
        if (empty($address)) return jsonFailed('该地址不存在');
        // if ($address->default == 1) return jsonFailed('默认地址不能被删除');
        DB::table('user_address')->where('id', $address->id)->delete();
        return jsonSuccess();
    }
}
