<?php


namespace App\Rpc\Service;


use Framework\SwServer\Pool\DiPool;
use App\Rpc\Contract\UserInterface;
use Framework\SwServer\Rpc\Annotation\RpcServiceRouter;

/**
 * Class UserServiceV2
 * @RpcServiceRouter(version="2.0")
 */
class RpcUserServiceV2 implements UserInterface
{

    public function getList(int $uid, string $type){
        $uid=$uid."V2";
        $type=$type."V2";
        var_dump($uid,$type,"__");
        return [$uid,$type];
    }
}