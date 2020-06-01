<?php

namespace App\Middleware;

use App\Service\UserService as userService;
use OpenTracing\Span;
use OpenTracing\Tracer;
use Framework\Traits\SpanStarter;
use Swoole\Http\Request;
use Framework\SwServer\Coroutine\CoroutineManager;
use App\Handler\RequestHandler as requestHandler;
use Framework\SwServer\Http\HttpJoinPoint;
use App\Annotation\Bean;
class RequestMiddleware
{
    /**
     * @Bean(name="App\Handler\RequestHandler")
     */
    public $handler = null;

    public function setHandler(requestHandler $requestHandler)
    {
        $this->handler = $requestHandler;
    }

    public function getHandler(){
        return $this->handler;
    }

    public function process(HttpJoinPoint $httpJoinPoint)
    {
        print_r($this->handler);
        $request = CoroutineManager::get('tracer.request');
        $response = $this->handler->handle($request);
        $response && CoroutineManager::set('tracer.response', $response);
        $response = $httpJoinPoint->process();
        return $response;
    }

}
