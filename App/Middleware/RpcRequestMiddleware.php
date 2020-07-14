<?php

namespace App\Middleware;

use App\Service\UserService as userService;
use Framework\SwServer\Pool\DiPool;
use OpenTracing\Span;
use OpenTracing\Tracer;
use Framework\Traits\SpanStarter;
use Swoole\Http\Request;
use Framework\SwServer\Coroutine\CoroutineManager;
use App\Handler\RpcRequestHandler ;
use Framework\SwServer\Http\HttpJoinPoint;
use App\Annotation\Bean;

use Framework\SwServer\Rpc\Server;
class RpcRequestMiddleware
{
    /**
     * @Bean(name="App\Handler\RpcRequestHandler")
     */
    public $handler = null;

    public function setHandler(RpcRequestHandler $requestHandler)
    {
        $this->handler = $requestHandler;
    }

    public function getHandler(){
        return $this->handler;
    }

    public function process(HttpJoinPoint $httpJoinPoint)
    {
        $this->handler=DiPool::getInstance()->getSingleton(RpcRequestMiddleware::class)->handler;
        $request = CoroutineManager::get('rpc.request');
        $response = $this->handler->handle($request);
        $response && CoroutineManager::set('rpc.response', $response);
        $response = $httpJoinPoint->process();
        return $response;
    }

}
