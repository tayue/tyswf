<?php


namespace App\Handler;


use App\Rpc\Contract\UserInterface;
use App\Rpc\Service\RpcUserService;
use Framework\SwServer\Coroutine\CoroutineManager;

use Framework\SwServer\Rpc\Contract\RequestInterface;

use Framework\SwServer\Pool\DiPool;
use Framework\SwServer\Rpc\Router\Router;
use Framework\SwServer\Rpc\Router\RouteRegister;
use Framework\Tool\Tool;
use function method_exists;
use Framework\SwServer\Rpc\Exception\RpcException;

class RpcRequestHandler
{
    public function handle(RequestInterface $request)
    {
        $response = CoroutineManager::get('rpc.response');
        $version = $request->getVersion();
        $interface = $request->getInterface();
        $method = $request->getMethod();
        $params = $request->getParams();
        var_dump($version,$interface,$method,$params);
//        $router = DiPool::getInstance()->getSingleton(Router::class);
//        list($status, $className) = $router->match($version, $interface);
//        if ($status != Router::FOUND) {
//            throw new RpcException(
//                sprintf('Route(%s-%s) is not founded!', $version, $interface)
//            );
//        }
//        $object = DiPool::getInstance()->getSingleton($className);
//        if (!$object instanceof $interface) {
//            throw new RpcException(
//                sprintf('Object is not instanceof %s', $interface)
//            );
//        }
//
//        if (!method_exists($object, $method)) {
//            throw new RpcException(
//                sprintf('Method(%s::%s) is not founded!', $interface, $method)
//            );
//        }
        //$data = Tool::call([$object, $method], $params);
        //return $response->setData($data);
        return $response;
    }

}