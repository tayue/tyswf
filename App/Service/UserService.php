<?php


namespace App\Service;


use Framework\SwServer\Pool\DiPool;

class UserService
{
    public function notify($orderData)
    {
        print_r($orderData);
        echo __CLASS__ . "/" . __METHOD__ . "\r\n";
    }

    public function findUser()
    {

        $userData = DiPool::getInstance()->get("db")->table('user')->find();
         
        return $userData;
    }
}