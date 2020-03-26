<?php
namespace AppTest\Helper;
use PHPUnit\Framework\Assert;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends \Codeception\Module
{
    /**
     * 这是我们扩展的方法
     */
    public function assertArrayHasKeys($keys, $array, $message = ''){
        foreach(explode(',', $keys) as $key){
            Assert::assertArrayHasKey($key, $array, '断言' . $key . '时失败，外部消息:' . $message);
        }
    }
}
