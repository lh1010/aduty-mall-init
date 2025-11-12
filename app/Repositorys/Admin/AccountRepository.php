<?php

namespace App\Repositorys\Admin;

use DB;

class AccountRepository
{
    public function getLoginAdmin()
    {
        $adminToken = '';
        $admin = [];

        if (Request()->isMethod('post')) {
            $adminToken = Request()->adminToken;
            if (empty($adminToken)) $adminToken = Request()->header('adminToken');
        } else {
            $adminToken = Cookie::get('adminToken');
        }
        if (empty($adminToken)) return $admin;

        $log = DB::table('admin_login_log')->where(['token' => $adminToken, 'status' => 1])->orderBy('id', 'desc')->first();
        if (empty($log)) return $admin;

        $select = ['admin.*'];
        $admin = DB::table('admin')->select($select)->where('admin.id', $log->admin_id)->where('admin.status', 1)->first();
        if (empty($admin)) return $admin;

        return $admin;
    }
}
