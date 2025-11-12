<?php

namespace App\Http\Middleware;
use Closure;
use DB;

class CheckUserLogin
{
    public function handle($request, Closure $next)
    {
        $user = getLoginUser();
        if (empty($user)) {
            if ($request->isMethod('post')) return jsonFailed('请先登录', 401);
            return redirect(url('/login_password'));
        }
        $request->attributes->add(['user' => $user]);
        return $next($request);
    }
}
