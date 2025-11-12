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
        $this->reportable(function (Throwable $e) {

        });

        $this->renderable(function (Throwable $e) {
            return $this->handleException($e);
        });
    }

    public function handleException(Throwable $e)
    {
        // api
        if (Request()->is("api/*")) {
            if ($e instanceof ValidationException) {
                $message = array_values($e->errors())[0][0];
                return jsonFailed($message);
            }
            return jsonFailed('服务异常');
        }

        // post
        if (Request()->isMethod('post')) {
            if ($e instanceof ValidationException) {
                $message = array_values($e->errors())[0][0];
                return jsonFailed($message);
            }
            return jsonFailed('服务异常', 500);
        }
    }
}
