<?php


namespace App\Annotation;

use Framework\SwServer\Annotation\AbstractBean;
use Doctrine\Common\Annotations\Annotation;
use Framework\SwServer\Pool\DiPool;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Bean extends AbstractBean
{

    public $name; //对象别名请用配置里的对象注册别名

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }


    public function getName()
    {
        return $this->name;
    }


    public function setName($name)
    {
        $this->name = $name;
    }

    public function get()
    {
        try {
            $obj = DiPool::getInstance()->get($this->name);
            if (!$obj) {
                return false;
            }
            return $obj;
        } catch (\Throwable $e) {
            return false;
        }
    }
}