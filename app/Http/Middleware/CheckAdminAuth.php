<?php

namespace App\Http\Middleware;
use Closure;
use Session;
use DB;

class CheckAdminAuth
{
    public function handle($request, Closure $next)
    {
        if ($this->exclude($request)) return $next($request);

        $admin = Session::get('admin');

        // 超级管理员
        if ($admin['admin']['id'] == Config('common.admin')['super_admin_id']) return $next($request);

        $full_action = $request->route()->getActionName();
        $array = explode('@', $full_action);
        $current_action = strtolower(end($array));
        $array = explode('\\', $array[0]);
        $current_controller = strtolower(end($array));

        // 无访问权限
        if (!isset($admin['powers'][$current_controller]) || !isset($admin['powers'][$current_controller][$current_action])) {
            if ($request->isMethod('post')) return jsonFailed('无访问权限', 400);
            return redirect(url('/admin/freeAccess/common/no_auth'));
        }

        return $next($request);
    }

    private function exclude($request)
    {
        $full_action = $request->route()->getActionName();
        $array = explode('@', $full_action);
        $current_action = end($array);
        $array = explode('\\', $array[0]);
        $current_controller = end($array);

        //dd($full_action);

        // 免权限文件夹
        if (in_array('FreeAccess', $array)) return true;

        // 指定免权限控制器
        $excludes = [
            'App\Http\Controllers\Admin\AccountController@login',
            'App\Http\Controllers\Admin\AccountController@logout',
            'App\Http\Controllers\Admin\HomeController@index',
            'App\Http\Controllers\Admin\HomeController@welcome',
            'App\Http\Controllers\Admin\UploadController@index',
        ];
        if (in_array($full_action, $excludes)) return true;

        return false;
    }
}
