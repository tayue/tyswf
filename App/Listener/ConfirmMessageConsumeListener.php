<?php

namespace App\Listener;

use App\Service\CommonService;
use App\Service\OrderService;
use App\Service\MessageService;
use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\SwServer\Event\EventHandlerInterface;
use Framework\SwServer\Event\EventInterface;
use Framework\SwServer\Pool\RedisPoolManager;
use Swoole\Coroutine as SwCoroutine;

//确认消息已被成功消费（被动方应用系统）
class ConfirmMessageConsumeListener implements EventHandlerInterface
{
    public $connectionRedis;
    public $messageService; //消息服务子系统（包含接口）
    public $orderService;
    public $connectionRabbit;

    public function __construct()
    {
        $this->orderService = new OrderService();
        $this->messageService = new MessageService();
    }

    public function getRedis()
    {
        $this->connectionRedis = CommonService::getRedis();
    }

    public function getRabbit()
    {
        $this->connectionRabbit = CommonService::getRabbit();
    }

    /**
     * @param 多进程处理尽可能快速的消费消息防止消息因没有及时消费被重复投递进入死队列中
     */
    public function handle(EventInterface $event)
    {
        echo CoroutineManager::getInstance()->getCoroutineId() . "###\r\n";
        $this->getRedis();
        //查询消费确认超时的消息（消息恢复子系统）
        $exchangeName = 'tradeExchange';
        $routeKey = '/trade';
        $queueName = 'trade';
        $this->messageService->consumeMessages($exchangeName, $queueName, $routeKey, function ($message) {
            $data = json_decode($message->body, true);
            echo "[".date("Y-m-d H:i:s")."] ".$data['msg_id'] . "=>" . $data['status'] . "\r\n";
            //1.记录消息任务是否完成（消费幂等:同一个任务执行10次跟执行一次的效果是一样）
            $statusJob = $this->connectionRedis->get("integrating_message_job:" . (string)$data['msg_id']);
            if ($statusJob == 2) { //已经消费成功了
                $this->messageService->ackMsg($data['msg_id']); //这个任务已经消费成功了
            } elseif ($statusJob == 1) { //任务正在执行
                var_dump("任务正在执行当中");
                return;
            } else {
                //执行任务当中,并且设置释放的时间
                $res = $this->connectionRedis->setex("integrating_message_job:" . $data['msg_id'], 15, 1); //分布式互斥锁
                if ($res) {
                    //SwCoroutine::sleep(0.5);//任务正在执行当中
                    //2.操作mysql更新积分(业务逻辑执行完毕)
                    echo "[".date("Y-m-d H:i:s")."] ".$data['msg_id'] . "######" . $data['status'] . "\r\n";
                    $this->connectionRedis->set("integrating_message_job:" . $data['msg_id'], 2);//执行任务完毕
                    $this->messageService->ackMsg($data['msg_id']); //这个任务已经消费成功了
                    //回应ack
                    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                }
            }
        });

    }


}