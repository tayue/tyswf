<?php

namespace App\Middleware;

use Framework\SwServer\Pool\DiPool;
use Framework\SwServer\Tracer\TracerFactory;
use OpenTracing\Span;
use OpenTracing\Tracer;
use Framework\Traits\SpanStarter;
use Swoole\Http\Request;
use Framework\SwServer\Coroutine\CoroutineManager;
use App\Handler\TracerHandler as requestHandler;
use Framework\SwServer\Http\HttpJoinPoint;
use App\Annotation\Bean;


class TracerMiddleware
{
    use SpanStarter;
    /**
     * @Bean(name="App\Handler\TracerHandler")
     */
    public $handler = null;

    public $tracer;

    public function __construct()
    {
        $container = DiPool::getInstance();
        $TracerFactory = $container->getSingleton(TracerFactory::class);
        $this->tracer = $TracerFactory->getTracer();
    }

    public function process(HttpJoinPoint $httpJoinPoint)
    {
        print_r($this->handler);
        $request = CoroutineManager::get('tracer.request');
        $span = $this->buildSpan($request);
        $response = $this->handler->handle($request);
        $response && CoroutineManager::set('tracer.response', $response);
        $response = $httpJoinPoint->process();
        $span->finish();
        $tracer = $this->tracer;
        defer(function () use ($tracer) {
            $tracer->flush();
        });
        return $response;
    }

    protected function buildSpan(Request $request): Span
    {
        $uri = $request->server['request_uri'];
        $span = $this->startSpan('request');
        $span->setTag('coroutine.id', (string)CoroutineManager::id());
        $span->setTag('request.path', (string)$uri);
        $span->setTag('request.method', $request->server['request_method']);
        foreach ($request->header as $key => $value) {
            $span->setTag('request.header.' . $key, $value);
        }
        return $span;
    }

}