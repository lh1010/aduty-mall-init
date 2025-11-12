<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use DB;
use App\Repositorys\Admin\DefaultRepository;

class DefaultController extends BaseController
{
    public function list(Request $request)
    {
        $params = $request->all();
        $results = app(DefaultRepository::class)->getList($params);;
        return view('admin.tbname.list', compact('results'));
    }

    public function show(Request $request)
    {
        $result = app(DefaultRepository::class)->getShow($request->id);
        if (empty($result)) abort(404);
        return view('admin.tbname.show', compact('result'));
    }

    public function create(Request $request)
    {
        return view('admin.tbname.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => ['required'],
        ];
        $messages = [
            'name.required' => '名字不能为空',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return jsonFailed($validator->errors()->first());

        $params = $request->all();
        $data = app(DefaultRepository::class)->setCreateUpdateParams($params);

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

    public function edit(Request $request)
    {
        $result = DB::table('tbname')->where(['id' => $request->id])->where('status', '<>', 99)->first();
        if (empty($result)) abort(404);
        return view('admin.tbname.edit', compact('result'));
    }

    public function update(Request $request)
    {
        $result = DB::table('tbname')->where(['id' => $request->id])->where('status', '<>', 99)->first();
        if (empty($result)) return jsonFailed('内容不存在');

        $rules = [
            'name' => ['required'],
        ];
        $messages = [
            'name.required' => '名字不能为空',
        ];
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) return jsonFailed($validator->errors()->first());

        $params = $request->all();
        $data = app(DefaultRepository::class)->setCreateUpdateParams($params);

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
        $result = DB::table('tbname')->where(['id' => $request->id])->where('status', '<>', 99)->first();
        if (empty($result)) return jsonFailed('内容不存在');
        DB::table('tbname')->where(['id' => $result->id])->update(['status' => 99]);
        return jsonSuccess();
    }

    public function audit(Request $request)
    {
        $result = DB::table('tbname')->where('id', $request->id)->where('status', '<>', 99)->first();
        if (empty($result)) return jsonFailed('内容不存在');

        if ($request->isMethod('post')) {
            DB::beginTransaction();
            try {
                DB::table('tbname')->where('id', $result->id)->update(['status' => $request->status]);
                DB::table('tbname_audit_log')->insert([
                    'tbname_id' => $result->id,
                    'status' => $request->status,
                    'message' => $request->message
                ]);
                DB::commit();
                return jsonSuccess();
            } catch (\Throwable $th) {
                DB::rollBack();
                return jsonFailed($th->getMessage());
            }
        }

        $logs = DB::table('tbname_audit_log')->where('tbname_id', $result->id)->orderBy('id', 'desc')->paginate();
        return view('admin.tbname.audit', compact('tbname', 'logs'));
    }
}
