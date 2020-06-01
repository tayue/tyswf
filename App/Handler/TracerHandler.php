<?php


namespace App\Handler;


use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\SwServer\Http\RequestHandlerInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;


class TracerHandler implements RequestHandlerInterface
{

    public function handle(Request $request): Response
    {
        $response = CoroutineManager::get('tracer.response');
        $uri = $request->server['request_uri'];
        echo "TracerHandler:".Date("Y-m-d H:i:s") . ",url:{$uri}\r\n";
        $response->header("response_time", Date("Y-m-d H:i:s"));
        return $response;
    }

}