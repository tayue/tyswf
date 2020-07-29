<?php

namespace App\Listener;

use App\Service\CommonService;
use App\Service\OrderService;
use App\Service\MessageService;
use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\SwServer\Event\EventHandlerInterface;
use Framework\SwServer\Event\EventInterface;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use Swoole\Coroutine\Http\Client;
use Framework\SwServer\Pool\RedisPoolManager;
use Swoole\Coroutine as SwCoroutine;

//
//分布式事务最大努力通知型
//确认消息已被成功消费（被动方应用系统）
class ConfirmMessageConsumeDelayListener implements EventHandlerInterface
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

    /*
     * @param 多进程处理尽可能快速的消费消息防止消息因没有及时消费被重复投递进入死队列中
     */
    public function handle(EventInterface $event)
    {
        //延迟几秒,注册自身才能调用
       // swoole_timer_tick(2000, function () {
            echo CoroutineManager::getInstance()->getCoroutineId() . "###\r\n";
            try {
                $this->getRabbit();
                //获取延迟任务消费
                $delayExchangeName = 'delayExchange';
                $delayRouteKey = '/delay';
                $delayQueueName = 'delay';

                $this->messageService->consumeMessages($delayExchangeName, $delayQueueName, $delayRouteKey, function ($message) {
                    echo date("Y-m-d H:i:s")."----\r\n";
                    \co::sleep(2);
                    $data = json_decode($message->body, true);
                    //按照阶梯投递
                    $data['notify_retries_number'] += 1;
                    if ($data['notify_retries_number'] >= 3) {
                        //写入到redis当中
                        var_dump("Faild Number More...");
                        $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                        return;
                    }

                    //执行业务逻辑(通知商户),根据url地址通知商户
                    $cli = new Client('192.168.99.88', 9501);
                    $cli->set(['timeout' => 1]);
                    $cli->post($data['notify_url'], $data);
                    if ('success' == $cli->body) {
                        var_dump("Notify Success...");
                    } else {
                        //继续投递延迟任务
                        $data['default_delay_time'] = $data['notify_rule'][$data['notify_retries_number']] * 1000;
                        $this->messageService->delayPublish($data);
                        var_dump("Notify Faild Continue Send Message...");
                    }
                    //回应ack
                    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
                }, false);


            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }

       // });
    }


}