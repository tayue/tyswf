<?php


namespace App\Annotation;

use Hyperf\Di\Annotation\Aspect;
use Framework\SwServer\Aop\AbstractAspect;
use Framework\SwServer\Aop\ProceedingJoinPoint;
use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class AfterAspect extends AbstractAspect
{
    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $result = $proceedingJoinPoint->process();
        if (is_string($result) || !$result) {
            $result .= "=>AfterAspect";
        } else if (is_numeric($result)) {
            $result += 1;
        }

        echo "---------AfterAspect start[" . Date("Y-m-d H:i:s") . "]-----------\r\n";

        // 切面切入后，执行对应的方法会由此来负责
        // $proceedingJoinPoint 为连接点，通过该类的 process() 方法调用原方法并获得结果
        // 在调用前进行某些处理

        echo "---------do something[" . Date("Y-m-d H:i:s") . "]-----------\r\n";


        echo "---------AfterAspect end[" . Date("Y-m-d H:i:s") . "]-----------\r\n";
        // 在调用后进行某些处理
        return $result;
    }
}