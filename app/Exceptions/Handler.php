<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        // reportable 注册异常上报回调，决定哪些异常需要被记录到日志或上报到外部监控
        // 返回 null 表示走 Laravel 默认上报逻辑，非 null 则干预自定义处理
        // 当前隐式返回 null，不干预，走默认上报/记录逻辑
        $this->reportable(function (Throwable $e) {

        });

        // renderable 注册异常渲染回调，决定异常最终以何种响应返回给客户端
        // 返回 null 表示不干预，走 Laravel 默认渲染，非 null 则干预自定义处理
        $this->renderable(function (Throwable $e) {
            // 验证异常始终走自定义格式，不受 debug 影响
            if ($e instanceof ValidationException) {
                return $this->handleException($e);
            }
            // 开发环境不拦截，让 Laravel 展示完整错误详情，方便调试
            if (config('app.debug')) {
                return;
            }
            // 生产环境走自定义兜底逻辑，隐藏异常细节
            // 统一返回格式
            // 可记录日志/消息通知
            return $this->handleException($e);
        });
    }

    public function handleException(Throwable $e)
    {
        // 验证异常统一处理
        if ($e instanceof ValidationException) {
            $message = array_values($e->errors())[0][0];
            return jsonFailed($message);
        }

        // api
        if (Request()->is("api/*")) {
            // 扩展日志/消息通知
            // todo
            return jsonFailed('服务异常');
        }

        // post
        if (Request()->isMethod('post')) {
            // 扩展日志/消息通知
            // todo
            return jsonFailed('服务异常');
        }
    }
}
