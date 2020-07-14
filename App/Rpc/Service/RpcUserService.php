<?php


namespace App\Rpc\Service;


use Framework\SwServer\Pool\DiPool;
use App\Rpc\Contract\UserInterface;
use Framework\SwServer\Rpc\Annotation\RpcServiceRouter;

/**
 * Class UserService
 * @RpcServiceRouter()
 */
class RpcUserService implements UserInterface
{
    public function getList(int $uid, string $type){
        return [$uid,$type];
    }
}