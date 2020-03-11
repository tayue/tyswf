<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/8
 * Time: 15:53
 */
namespace App\Controller;
use Framework\SwServer\ServerController;
class IndexController extends ServerController
{
   public function indexAction(){
       print_r($_GET);
     //  echo "App\\Controller\\IndexController\\indexAction";
   }


}
