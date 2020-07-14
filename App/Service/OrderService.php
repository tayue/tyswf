<?php


namespace App\Service;


use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\SwServer\Pool\RedisPoolManager;

//订单服务
class OrderService
{
    public $connectionRedis;

    public function getRedis()
    {
        $this->connectionRedis=CommonService::getRedis();
    }

    public function putRedis(){
        $this->connectionRedis && RedisPoolManager::getInstance()->put($this->connectionRedis);
    }

    public function setRedis($redisConnection)
    {
        $this->connectionRedis = $redisConnection;
    }

    public function create($orderData)
    {
        print_r($orderData);
        echo __CLASS__ . "/" . __METHOD__ . "\r\n";
    }

    public function update($data): array
    {
        $res = ['status' => 0, 'result' => '订单状态更新失败'];
        $this->getRedis();
        //业务执行成功
        //调用mysql更新信息（业务逻辑）
        //确认某个任务执行成功
        if (!$this->connectionRedis) {
            return $res;
        }

        if ($this->connectionRedis->hset("order_message_job", (string)$data['msg_id'], '1')) {
            $res['status'] = 1;
            $res['result'] = '订单状态更新成功';
            return $res;
        }
        return $res;
    }

    public function confirmStatus($msg_id)
    {
        $res = ['status' => 0, 'result' => '订单状态更新失败'];
        $this->getRedis();
        $resultVal = $this->connectionRedis->hget("order_message_job", (string)$msg_id);
        if ($resultVal) {
            $res['status'] = 1;
            $res['result'] = '订单状态更新成功';
        }
        //$this->putRedis();
        return $res;
    }

}