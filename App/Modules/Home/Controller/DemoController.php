<?php


namespace App\Modules\Home\Controller;

use App\Service\Util;
use Framework\SwServer\Router\Annotation\Controller;
use Framework\Tool\Tool;
use Framework\SwServer\Router\Annotation\RequestMapping;
/**
 * @Controller(prefix="demo")
 */
class DemoController
{
    public $util;

    public function __construct(Util $util)
    {
        $this->util = $util;
    }

    /**
     * @RequestMapping(path="index/{id:\d+}", methods="get,post,put,delete")
     */
    public function indexAction(Tool $tool,$id){
        print_r($tool);
        print_r($this->util->display());
        echo '/home/demo/index/'.$id;
    }

}