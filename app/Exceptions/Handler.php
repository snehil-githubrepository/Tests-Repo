<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->renderable(function (ControllerException $e, $request) {
            return $e->render($request);
        });

        $this->renderable(function (NotAuthorizedException $e, $request) {
            return $e->render($request);
        });

        $this->renderable(function (TestException $e, $request) {
            return $e->render($request);
        });
    }
}
