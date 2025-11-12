<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use DB;
use App\Repositorys\DefaultRepository;

class DefaultController extends BaseController
{
    public function __construct(Request $request)
    {
        $this->middleware('CheckUserLogin')->except([
            'getList',
            'getShow',
        ]);
    }

    public function getList(Request $request)
    {
        $params = $request->all();
        $results = app(DefaultRepository::class)->getList($params);;
        return jsonSuccess($results);
    }

    public function getShow(Request $request)
    {
        $id = $request->id;
        $params = $request->all();
        $result = app(DefaultRepository::class)->getShow($id, $params);;
        return jsonSuccess($result);
    }

    public function store(\App\Http\Requests\Default\store $request)
    {
        $loginUser = getLoginUser();
        $params = $request->all();
        $data = app(DefaultRepository::class)->setStoreUpdateParams($params);
        $data['user_id'] = $loginUser->id;

        DB::beginTransaction();
        try {
            $id = DB::table('tbname')->insertGetId($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function update(\App\Http\Requests\Default\update $request)
    {
        $loginUser = getLoginUser();

        $result = DB::table('tbname')->where(['id' => $request->id, 'user_id' => $loginUser->id])->where('status', '<>', 99)->first();
        if (empty($result)) return jsonFailed('内容不存在');

        $params = $request->all();
        $data = app(DefaultRepository::class)->setStoreUpdateParams($params);

        DB::beginTransaction();
        try {
            DB::table('tbname')->where('id', $result->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $loginUser = $request->get('user');
        $result = DB::table('tbname')->where(['id' => $request->id, 'user_id' => $loginUser->id])->where('status', '<>', 99)->first();
        if (empty($result)) return jsonFailed('内容不存在');
        DB::table('tbname')->where(['id' => $result->id])->update(['status' => 99]);
        return jsonSuccess();
    }

    public function getMyList(Request $request)
    {
        $loginUser = getLoginUser();
        $params = $request->all();
        $params['user_id'] = $loginUser->id;
        $results = app(DefaultRepository::class)->getList($params);;
        return jsonSuccess($results);
    }

    public function getMyShow(Request $request)
    {
        $loginUser = getLoginUser();
        $result = app(DefaultRepository::class)->getShow($request->id, ['user_id' => $loginUser->id]);;
        return jsonSuccess($result);
    }
}
