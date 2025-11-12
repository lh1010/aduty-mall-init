<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;

class CdkeyController extends BaseController
{
    public function list(Request $request)
    {
        $params = $request->all();
        $query = DB::table('cdkey');
        if (isset($params['k']) && !empty($params['k'])) {
            $query->where(function($query) use ($params) {
                $query->where('cdkey.id', $params['k'])
                    ->orWhere('cdkey.remark', 'like', "%" . $params['k'] . "%");
            });
        }
        if (isset($params['used_status']) && !empty($params['used_status'])) {
            $query->where('cdkey.used_status', $params['used_status']);
        }
        $query->orderBy('id', 'desc');
        $cdkeys = $query->paginate();
        return view('admin.cdkey.list', compact('cdkeys'));
    }

    public function create()
    {
        return view('admin.cdkey.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            if (DB::table('cdkey')->where('key', $request->key)->first()) {
                return jsonFailed('该卡密内容已存在');
            }
            $data = $this->setStoreUpdateParams($request->all());
            DB::table('cdkey')->insertGetId($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function edit(Request $request)
    {
        $cdkey = DB::table('cdkey')->where('id', $request->id)->first();
        if (empty($cdkey)) abort(404);
        return view('admin.cdkey.edit', compact('cdkey'));
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $cdkey = DB::table('cdkey')->where('id', $request->id)->first();
            if (empty($cdkey)) return jsonFailed('数据不存在');
            if (DB::table('cdkey')->where('key', $request->key)->where('id', '<>', $cdkey->id)->first()) {
                return jsonFailed('该卡密内容已存在');
            }
            $data = $this->setStoreUpdateParams($request->all());
            DB::table('cdkey')->where('id', $cdkey->id)->update($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }

    public function delete(Request $request)
    {
        DB::table('cdkey')->where('id', $request->id)->delete();
        return jsonSuccess();
    }

    public function setStoreUpdateParams($params = [])
    {
        $data = [];
        if (isset($params['type'])) $data['type'] = $params['type'];
        if (isset($params['key'])) $data['key'] = $params['key'];
        if (isset($params['gold']) && is_numeric($params['gold'])) $data['gold'] = $params['gold'];
        if (isset($params['assign_user_id'])) $data['assign_user_id'] = $params['assign_user_id'];
        if (isset($params['end_date'])) $data['end_date'] = $params['end_date'];
        if (isset($params['remark'])) $data['remark'] = $params['remark'];
        if (isset($params['status'])) $data['status'] = $params['status'];
        return $data;
    }

    public function batch_create()
    {
        return view('admin.cdkey.batch_create');
    }

    public function batch_store(Request $request)
    {
        $keys = str_replace('，', ",", $request->keys);
        if (empty($keys)) return jsonFailed('卡密内容不能为空');
        $keys = explode(",", $keys);

        $cdkeys = DB::table('cdkey')->whereIn('key', $keys)->get()->toArray();
        if (!empty($cdkeys)) {
            $str = "以下卡密内容已存在：<br/>";
            foreach ($cdkeys as $key => $value) {
                $str .= $value->key . "<br/>";
            }
            return jsonFailed($str);
        }

        DB::beginTransaction();
        try {
            $data = [];
            foreach ($keys as $key => $value) {
                $data[$key] = [
                    'type' => $request->input('type', ''),
                    'key' => $value,
                    'gold' => is_numeric($request->gold) ? $request->gold : 1,
                    'assign_user_id' => $request->input('assign_user_id', ''),
                    'end_date' => $request->input('end_date', ''),
                    'remark' => $request->input('remark', ''),
                    'status' => $request->input('status', 1),
                ];
            }
            DB::table('cdkey')->insert($data);
            DB::commit();
            return jsonSuccess();
        } catch (\Throwable $th) {
            DB::rollBack();
            return jsonFailed($th->getMessage());
        }
    }
}
