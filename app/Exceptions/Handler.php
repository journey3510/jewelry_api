<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;
use App\Library\Tools\Common\Common;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        // if ($exception instanceof NotFoundHttpException) {
        //     return response(['ServerTime' => time(), 'ServerNo' => 404, 'ResultData' => '404']);
        // }
        // 消息队列 体验更加
        // $webhook = "";
        // $time = date('Y-m-d H:i');
        // $data = [
        //     "msgtype" =>  "markdown",
        //     "markdown" => [
        //         "content" => "错误代码行数: <font color='warning'>{$exception->getLine()}</font>. \r
        //          > 报错文件: <font color='comment'>{$exception->getFile()}</font>
        //          > 报错信息: `{$exception->getMessage()}`
        //          > 服务器消息推送时间: <font color='comment'>{$time}</font>"
        //     ]
        // ];
        // $data_string = json_encode($data);
        // Common::curl($webhook, $data_string);
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // if ($exception instanceof NotFoundHttpException) {
        //     return response(['ServerTime' => time(), 'ServerNo' => 404, 'ResultData' => '404']);
        // }
        return parent::render($request, $exception);
    }
}
