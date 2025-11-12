<?php

namespace App\Http\Middleware;
use Closure;
use DB;
use App\Repositorys\Admin\AccountRepository;

class CheckAdminApiLogin
{
    public function handle($request, Closure $next)
    {
        if ($this->exclude($request)) return $next($request);

        $admin = app(AccountRepository::class)->getLoginAdmin();

        if (empty($admin)) {
            if ($request->isMethod('post')) return jsonFailed('请先登录', 401);
            return redirect(url('/login_password'));
        }

        $request->attributes->add(['admin' => $admin]);

        return $next($request);
    }

    private function exclude($request)
    {
        $currentController = $request->route()->getAction('controller');

        $excludes = [
            'App\Http\Controllers\AdminApi\AccountController@login',
            'App\Http\Controllers\AdminApi\AccountController@logout'
        ];

        if (in_array($currentController, $excludes)) return true;

        return false;
    }
}
