<?php


namespace App\Annotation;

use Hyperf\Di\Annotation\Aspect;
use Framework\SwServer\Aop\AbstractAspect;
use Framework\SwServer\Aop\ProceedingJoinPoint;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class ClassAspect extends AbstractAspect
{

    /**
     * @var string
     */
    public $type=0;


    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        echo "---------ClassAspect start[" . Date("Y-m-d H:i:s") . "]-----------\r\n";

        // 切面切入后，执行对应的方法会由此来负责
        // $proceedingJoinPoint 为连接点，通过该类的 process() 方法调用原方法并获得结果
        // 在调用前进行某些处理
        if($this->type==1){
            echo "open!\r\n";
        }else{
            echo "close!\r\n";
        }
        $result = $proceedingJoinPoint->process();
        var_dump($result);
        echo "---------ClassAspect end[" . Date("Y-m-d H:i:s") . "]-----------\r\n";
        // 在调用后进行某些处理
        return $result;
    }
}