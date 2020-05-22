<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/14
 * Time: 13:40
 */

namespace App\Service;


use Framework\SwServer\ServerManager;
use Framework\SwServer\WebSocket\WST;
use App\Service\Crypt;
use Framework\SwServer\Pool\DiPool;

class User
{
    public $arr = [];

    public $crypt;

    public $name="userService";
    public function __construct(Crypt $crypt)
    {
        $this->crypt=$crypt;
        $this->name=$this->name."=>".date("Y-m-d H:i:s");
    }

    public function findUser()
    {
 
        $userData = DiPool::getInstance()->get("db")->table('user')->find();
        $this->crypt->display();
        return $userData;
    }

    public function display(){
        $this->crypt->display();
        echo __CLASS__."==".__METHOD__;
    }

}
