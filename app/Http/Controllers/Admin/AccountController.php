<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\BaseController;
use Illuminate\Http\Request;
use DB;

class AccountController extends BaseController
{
    public function login(Request $request)
    {
        if ($request->isMethod('post')) {
            $username = $request->username;
            $password = md5($request->password);
            $admin = DB::table('admin')
                    ->where('username', $username)
                    ->where('password', $password)
                    ->where('status', '<>', 99)
                    ->first();

            if (empty($admin)) return jsonFailed('用户名或密码错误');
            if ($admin->status == 0) return jsonFailed('该账号已关闭');

            $data_log = [];
            $data_log['admin_id'] = $admin->id;
            $data_log['ip'] = Request()->ip();
            $data_log['token'] = md5($request->password . $admin->id . time() . rand(1000, 9999));
            DB::table('admin_login_log')->insert($data_log);

            // 需要存储seesion的数据
			$token = $data_log['token'];
            $role_ids = DB::table('admin_to_role')->where('admin_id', $admin->id)->pluck('role_id')->toArray();
            $action_ids = DB::table('admin_role_to_action')->whereIn('role_id', $role_ids)->pluck('action_id')->toArray();
            if ($admin->id == Config('common.admin')['super_admin_id']) {
                $actions = DB::table('admin_action')->where('status', 1)->orderBy('sort', 'desc')->get()->toArray();
            } else {
                $actions = DB::table('admin_action')->whereIn('id', $action_ids)->where('status', 1)->orderBy('sort', 'desc')->get()->toArray();
            }
            // 权限
            $powers = $this->tree_power($actions);
            // 菜单
            $menus = $this->tree_menu($actions);
            $data_session = [
                'admin' => [
                    'id' => $admin->id,
                    'username' => $admin->username,
                    'realname' => $admin->realname,
                    'token' => $token,
                    'role_ids' => $role_ids,
                    'action_ids' => $action_ids,
                ],
                'powers' => $powers,
                'menus' => $menus,
            ];
            session(['admin' => $data_session]);

            return jsonSuccess();
        }
        return view('admin.account.login');
    }

    private function tree_menu($data, $parent_id = 0)
    {
        $data_return = [];
        foreach ($data as $key => $value) {
            if ($value->parent_id == $parent_id) {
                $value->child = $this->tree_menu($data, $value->id);
                $data_return[] = $value;
            }
        }
        return $data_return;
    }

    private function tree_power($data)
    {
        $data_return = [];
        foreach ($data as $key => $value) {
            if ($value->controller) {
                $actions = explode(',', $value->actions);
                foreach ($actions as $key_action => $value_action) {
                    $controller = strtolower(trim($value->controller));
                    $action = strtolower(trim($value_action));
                    $data_return[$controller][$action] = $action;
                }
            }
        }
        return $data_return;
    }

    public function logout()
    {
        $admin = session('admin')['admin'];
        DB::table('admin_login_log')->where('admin_id', $admin['id'])->update(['status' => 0]);
        session()->pull('admin', null);
        return jsonSuccess();
    }
}
