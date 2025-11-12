<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;

class AdminController extends BaseController
{
    public function list()
    {
        $admins = DB::table('admin')->where('status', '<>', 99)->paginate();
        return view('admin.admin.list', compact('admins'));
    }

    public function create()
    {
        $roles = DB::table('admin_role')->get()->toArray();
        return view('admin.admin.create', compact('roles'));
    }

    public function store(Request $request)
    {
        if (DB::table('admin')->where('username', $request->username)->first()) {
            return jsonFailed('该用户名已存在');
        }

        $params = $request->all();
        $data = $this->setCreateUpdateParams($params);
        $admin_id = DB::table('admin')->insertGetId($data);

        if (isset($params['role_ids']) && !empty($params['role_ids'])) {
            $data = [];
            foreach ($params['role_ids'] as $key => $value) {
                $data[$key]['admin_id'] = $admin_id;
                $data[$key]['role_id'] = $value;
            }
            DB::table('admin_to_role')->insert($data);
        }
        return jsonSuccess();
    }

    public function edit(Request $request)
    {
        $admin = DB::table('admin')->where('id', $request->id)->first();
        $admin->role_ids = DB::table('admin_to_role')->where('admin_id', $admin->id)->pluck('role_id')->toArray();
        $roles = DB::table('admin_role')->get()->toArray();
        return view('admin.admin.edit', compact('admin', 'roles'));
    }

    public function update(Request $request)
    {
        if (DB::table('admin')->where('username', $request->username)->where('id', '<>', $request->id)->first()) {
            return jsonFailed('该用户名已存在');
        }

        $params = $request->all();
        $data = $this->setCreateUpdateParams($params);
        DB::table('admin')->where('id', $request->id)->update($data);

        DB::table('admin_to_role')->where('admin_id', $request->id)->delete();
        if (isset($params['role_ids']) && !empty($params['role_ids'])) {
            $data = [];
            foreach ($params['role_ids'] as $key => $value) {
                $data[$key]['admin_id'] = $request->id;
                $data[$key]['role_id'] = $value;
            }
            DB::table('admin_to_role')->insert($data);
        }
        return jsonSuccess();
    }

    private function setCreateUpdateParams($params = [])
    {
        $data = [];
        if (isset($params['username'])) $data['username'] = $params['username'];
        if (isset($params['password']) && !empty($params['password'])) $data['password'] = md5($params['password']);
        if (isset($params['realname'])) $data['realname'] = $params['realname'];
        if (isset($params['email'])) $data['email'] = $params['email'];
        if (isset($params['phone'])) $data['phone'] = $params['phone'];
        if (isset($params['remark'])) $data['remark'] = $params['remark'];
        if (isset($params['status'])) $data['status'] = $params['status'];
        return $data;
    }

    public function delete(Request $request)
    {
        if ($request->id == 1) return jsonFailed('超级管理员不能被删除');
        DB::table('admin')->where(['id' => $request->id])->update(['status' => 99]);
        return jsonSuccess();
    }
}
