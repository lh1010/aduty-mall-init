<?php

namespace App\Http\Middleware;
use Closure;
use Session;
use DB;

class CheckAdminLogin
{
    public function handle($request, Closure $next)
    {
        if ($this->exclude($request)) return $next($request);

        $admin = Session::get('admin');
        if (empty($admin)) {
            if ($request->isMethod('post')) return jsonFailed('请先登录', 401);
            return redirect(url('/admin/login'));
        }

        $admin = $admin['admin'];
        if (!empty($admin)) {
            $token = DB::table('admin_login_log')
            ->where('admin_id', $admin['id'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->pluck('token')
            ->first();
        }
        if (empty($token)) {
            if ($request->isMethod('post')) return jsonFailed('请先登录', 401);
            return redirect(url('/admin/login'));
        }
        $request->attributes->add(['admin' => $admin]);
        return $next($request);
    }

    private function exclude($request)
    {
        $currentController = $request->route()->getAction('controller');
        $excludes = [
            'App\Http\Controllers\Admin\AccountController@login',
            'App\Http\Controllers\Admin\AccountController@logout'
        ];
        if (in_array($currentController, $excludes)) return true;
        return false;
    }
}
