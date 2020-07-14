<?php


namespace App\Rpc\Contract;


interface UserInterface
{

    public function getList(int $uid, string $type);
}