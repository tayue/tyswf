<?php


namespace App\Modules\Home\Controller;
use Framework\SwServer\ServerManager;
use Framework\SwServer\ServerController;
class TestController  extends ServerController
{
   public function indexAction(){
       echo "indexAction\r\n";
       $users=ServerManager::getApp()->userService->findUser();
       print_r($users);
   }
}