<?php

namespace App\Listener;


use App\Process\ConsumeProcessPool;

use App\Service\CommonService;
use App\Service\OrderService;
use App\Service\MessageService;
use App\Service\RabbitMqService;
use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\SwServer\Event\EventHandlerInterface;
use Framework\SwServer\Event\EventInterface;
use Framework\SwServer\Pool\RedisPoolManager;
use Swoole\Coroutine as SwCoroutine;

class MessageConsumeOverTimeListener implements EventHandlerInterface
{
    public $connectionRedis;
    public $messageService; //消息服务子系统（包含接口）
    public $orderService;

    public function getRedis()
    {
        $this->connectionRedis = CommonService::getRedis();
    }

    /**
     * @param \Framework\SwServer\Event\EventInterface $event
     */
    public function handle(EventInterface $event)
    {
        //查询消费确认超时的消息（消息恢复子系统）
        $this->getRedis();
        $time = 1; //超时任务
        $OverTimeMsgQueueName = 'MessageConsumeOverTimeListener:msgQueue';
        swoole_timer_tick(10000, function () use ($time, $OverTimeMsgQueueName) { //默认10秒钟
            echo "[" . date('Y-m-d H:i:s') . "] MessageConsumeOverTimeListener:Start\r\n";
            $msgIds = $this->connectionRedis->zRangeByScore('message_system_time', "-inf", (string)(time() - $time));
            print_r($msgIds);
            if ($msgIds) { //将消息id插入消息队列
                foreach ($msgIds as $msgId) {
                    //CAS,事务性操作
                    $result = $this->connectionRedis->transaction(function ($tx) use ($msgId, $OverTimeMsgQueueName) {
                        $tx->lpush($OverTimeMsgQueueName, (string)$msgId);
                    });
                    if ($result[0] == false) {
                        echo "{$msgId} Over Message Save Faild !!\r\n";
                    }
                }
            }
            echo "[" . date('Y-m-d H:i:s') . "] MessageConsumeOverTimeListener:End\r\n";

        });


    }
}