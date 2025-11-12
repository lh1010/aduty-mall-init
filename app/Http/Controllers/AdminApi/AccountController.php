<?php

namespace App\Http\Controllers\AdminApi;

use Illuminate\Http\Request;
use DB;
use App\Repositorys\Admin\AccountRepository;

class AccountController extends BaseController
{
    public function login(Request $request)
    {
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

        $returnData = [
            'admin' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'realname' => $admin->realname,
            ],
            'admin_token' => $data_log['token']
        ];

        return jsonSuccess($returnData);
    }

    public function logout(Request $request)
    {
        $admin = app(AccountRepository::class)->getLoginAdmin();
        if (empty($admin)) return jsonSuccess();

        DB::table('admin_login_log')
            ->where(['admin_id' => $admin->id])
            ->update(['status' => 0]);
        return jsonSuccess();
    }
}
