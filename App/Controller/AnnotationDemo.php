<?php


namespace App\Controller;
use App\Annotation\AnnotatedDescription;
use App\Annotation\BeforeAspect;
use App\Annotation\AfterAspect;
use App\Annotation\Bean;
use App\Service\UserService;
use Framework\SwServer\Router\Annotation\Controller;
use Framework\SwServer\Router\Annotation\RequestMapping;
/**
 * @AnnotatedDescription("这是一个用于展示Annotation类的例子。")
 * @Controller(prefix="user")
 */
class AnnotationDemo
{
    /**
     * @Bean(name="userService")
     */
    private $property;

    /**
     * @AnnotatedDescription(value="啦啦")
     * @var string
     */
    protected $extra;

    public function setProperty($obj){
        $this->property=$obj;
    }


    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @RequestMapping(path="index/{id:\d+}", methods="get,post,put,delete")
     */
    public function indexAction($id){
        echo '/user/index/'.$id;
    }

    /**
     * @RequestMapping(path="test", methods="get,post,put,delete,header")
     */
    public function testAction(){
        echo '/user/test';
    }

    /**
     * @BeforeAspect()
     * @AnnotatedDescription(desc="test", type="getter")
     */
    public function test(){
        __CLASS__."=>".__METHOD__."\r\n";
    }

    /**
     * @BeforeAspect()
     * @AfterAspect()
     */
    public function getAop()
    {
        return "getAop";
    }
}