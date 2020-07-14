<?php


namespace App\Service;


use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\SwServer\Pool\RabbitPoolManager;
use Framework\SwServer\Pool\RedisPoolManager;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Framework\Core\Redis;

class CommonService
{

    public static $config = [];

    public static function setConfig($config)
    {
        self::$config = $config;
    }

    public static function setRedis($key = 'redis', $time = 0.1)
    {
        $config= isset(self::$config['redis_pool']) ? self::$config['redis_pool'] : [];
        $redisPoolManager = RedisPoolManager::getInstance($config);
        $resourceData=RedisPoolManager::getInstance()->get($time);
        if($resourceData && RedisPoolManager::checkIsConnection($resourceData)){
            $redis = CoroutineManager::set($key, $resourceData); // get context of this coroutine
            RedisPoolManager::getInstance(self::$config['redis_pool'])->clearSpaceResources();
            defer(function () use ($redisPoolManager, $redis) {
                //echo "--------------RedisPoolManager Collection------------------\r\n";
                $redisPoolManager->put($redis);
                //echo "[" . date('Y-m-d H:i:s') . "] Current Use RedisPoolManager Connetction Look Nums:" . $redisPoolManager->getLength() . ",currentNum:" . $redisPoolManager->getCurrentConnectionNums() . PHP_EOL;
            });
        }
    }

    public static function setRabbit($key = 'rabbit', $time = 0.1)
    {
        $config= isset(self::$config['rabbit_pool']) ? self::$config['rabbit_pool'] : [];
        $rabbitPoolManager = RabbitPoolManager::getInstance($config);
        $resourceData=RabbitPoolManager::getInstance()->get($time);
        if($resourceData && RabbitPoolManager::checkIsConnection($resourceData)){
            $rabbit = CoroutineManager::set($key,$resourceData ); // get context of this coroutine
            RabbitPoolManager::getInstance(self::$config['rabbit_pool'])->clearSpaceResources();
            defer(function () use ($rabbitPoolManager, $rabbit) {
                //echo "--------------RabbitPoolManager Collection------------------\r\n";
                $rabbitPoolManager->put($rabbit);
                //echo "[" . date('Y-m-d H:i:s') . "] Current Use RabbitPoolManager Connetction Look Nums:" . $rabbitPoolManager->getLength() . ",currentNum:" . $rabbitPoolManager->getCurrentConnectionNums() . PHP_EOL;
            });
        }

    }

    public static function getRedis($key = 'redis', $time = 0.1)
    {
        $inCoroutine = CoroutineManager::inCoroutine();
        if ($inCoroutine) {
            $coroRedis = CoroutineManager::get($key, null);
            //print_r($coroRedis);
            if ($coroRedis && RedisPoolManager::checkIsConnection($coroRedis)) {
               return $coroRedis;
            }
            self::Warn();
            $resourceData = RedisPoolManager::getInstance()->get($time);
            if (!$resourceData) {
                throw new \Exception("Not Has Redis Pool Connection!!!");
            }
            if(RedisPoolManager::checkIsConnection($resourceData)){
                CoroutineManager::set($key,$resourceData);
            }else{
                throw new \Exception("Not Has Right Redis Pool Connection!!!");
            }
        } else { //非协程环境
            $resourceData = new Redis(self::$config['redis_pool']);
            $resourceData = $resourceData->getConnection();
            if (!$resourceData) {
                throw new \Exception("Not Has Redis Connection!!!");
            }
        }
        return $resourceData;
    }

    public static function getRabbit($key = 'rabbit', $time = 0.1)
    {
        $inCoroutine = CoroutineManager::inCoroutine();
        if ($inCoroutine) {
            $coroRabbit = CoroutineManager::get($key, null);
            if ($coroRabbit && $coroRabbit->isConnected()){
                return $coroRabbit;
            }
            self::Warn();
            $resourceData = RabbitPoolManager::getInstance()->get($time);
            if (!$resourceData) {
                throw new \Exception("Not Has Rabbit Pool Connection!!!");
            }
            if($resourceData->isConnected()){
                CoroutineManager::set($key,$resourceData);
            }else{
                throw new \Exception("Not Has Right Rabbit Pool Connection!!!");
            }

        } else {
            $resourceData = new AMQPStreamConnection(self::$config['rabbit_pool']['host'], self::$config['rabbit_pool']['port'], self::$config['rabbit_pool']['user'], self::$config['rabbit_pool']['password'], self::$config['rabbit_pool']['vhost']);
            if (!$resourceData) {
                //连接失败，抛弃常
                throw new \Exception("failed to connect Rabbitmq server.");
            }
        }
        return $resourceData;
    }

    public static function Warn()
    {
        echo __METHOD__ . "ReGet Pool Resource !!\r\n";
    }

}