<?php


namespace App\Service;

use App\Listener\MessageConsumeOverTimeListener;
use App\Listener\ConfirmMessageConsumeListener;
use Framework\SwServer\Event\EventManager;
use Framework\SwServer\Pool\RabbitPoolManager;
use Framework\SwServer\Pool\RedisPoolManager;

use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use Swoole\Coroutine as SwCoroutine;
use Framework\SwServer\Coroutine\CoroutineManager;

/**
 * 消息子系统服务
 *
 */
class MessageService
{
    public $connectionRedis;

    public $connectionRabbit;

    public $orderService;

    public function __construct()
    {
        $this->orderService = new OrderService();
    }

    public static function MessageConsumeOverTimeHandle()
    {
        echo "Trigger MessageConsumeOverTimeHandle\r\n";
        $eventManager = new EventManager(); //全局的事件管理器
        $messageConsumeOverTimeListener = new MessageConsumeOverTimeListener();
        $eventManager->addListener($messageConsumeOverTimeListener, ["MessageConsumeOverTimeHandle" => 1]);
        $eventManager->trigger("MessageConsumeOverTimeHandle", null, []);
    }

    public static function ConfirmMessageConsumeListener($processIndex)
    {
        echo "Trigger ConfirmMessageConsumeListener:{$processIndex}\r\n";
        CommonService::setRedis();
        CommonService::setRabbit();
        $eventManager = new EventManager(); //全局的事件管理器
        $ConfirmMessageConsumeListener = new ConfirmMessageConsumeListener();
        $eventManager->addListener($ConfirmMessageConsumeListener, ["ConfirmMessageConsumeListener" => 1]);
        $eventManager->trigger("ConfirmMessageConsumeListener", null, []);
    }

    public function getRedis()
    {
        $this->connectionRedis = CommonService::getRedis();
    }

    public function putRedis()
    {
        $this->connectionRedis && RedisPoolManager::getInstance()->put($this->connectionRedis);
    }

    public function putRabbit()
    {
        $this->connectionRabbit && RabbitPoolManager::getInstance()->put($this->connectionRabbit);
    }

    public function getRabbit()
    {
        $this->connectionRabbit = CommonService::getRabbit();
    }

    //存储预发送消息
    public function prepareMsg($prepareMsgData): array
    {
        $res = ['status' => 0, 'result' => '预发送消息失败'];
        try {
            $this->getRedis();
            if (!$this->connectionRedis) {
                return $res;
            }
            $msg_id = $prepareMsgData['msg_id'];
            //CAS,事务性操作
            //存数据，还要存时间，依据时间查找超时的任务
            $result = $this->connectionRedis->transaction(function ($tx) use ($msg_id, $prepareMsgData) {
                $tx->hset("message_system", (string)$msg_id, json_encode($prepareMsgData));
                $tx->zAdd("message_system_time", $prepareMsgData['create_time'], (string)$msg_id);
            });
            if ($result[0] == false) {
                return ['status' => 0, 'result' => '预发送消息失败'];
            }
            $res['status'] = 1;
            $res['result'] = '预发送消息成功';
            return $res;
        } catch (\Throwable $e) {
            $res['result'] = $e->getMessage();
            echo $e->getMessage();
        }
        return $res;
    }

    public function ConsumeOneOverMessage($msgId)
    {
        $data = $this->connectionRedis->hget('message_system', (string)$msgId);
        if (!empty($data)) {
            $data = json_decode($data, true);
            if ($data['status'] == 2) { //状态为2代表的是已投递，超时没有被正确消费（消息恢复系统）
                //尝试重新投
                //如果投递的次数超过最大值,删除任务,并且存到单独存到redis一个队列当中
                if ($data['message_retries_number'] >= 2) {
                    var_dump($data['message_retries_number'], "投递失败,手动重试");
                    //可以封装成服务
                    $this->connectionRedis->transaction(function ($redis) use ($msgId, $data) {
                        $redis->hdel("message_system", (string)$msgId);
                        $redis->zrem("message_system_time", (string)$msgId);
                        //放在某个队列当中,在消息管理子系统当中可以手动恢复
                        $redis->lPush("message_system_dead", json_encode($data));
                    });
                }
                $this->confirmMsgToSend($msgId, 2); //再次投递消息业务
            } elseif ($data['status'] == 1) { //消息状态子系统（已经进入消息子系统但是未投递的）
                $stateJob = $this->orderService->confirmStatus($msgId);
                //1.查询任务结果(主动方任务是成功的，第一次投递到被动方的服务)
                if ($stateJob['status'] == 1) { //订单状态更新成功重新投递消息
                    $this->confirmMsgToSend($msgId, 1); //投递消息业务
                } elseif ($stateJob['status'] == 0) { //如果订单状态更新失败当前任务是失败的任务,删掉
                    //3.任务失败（删除任务）删除消息存储
                    $this->ackMsg($msgId);
                }
            }
            //判断任务的状态是预发送，并且确认消息状态，如果主动方任务成功，我们就投递，否则删除
            //确认业务状态，业务成功投递，业务失败删除
        }
    }

    public function ConsumeMessage($processIndex, $config = [])
    {

        echo CoroutineManager::getInstance()->getCoroutineId() . "@@@\r\n";
        CommonService::setConfig($config);
        CommonService::setRedis();
        CommonService::setRabbit();
        echo $processIndex . "@@@@@@@@@@@@@@\r\n";
        $OverTimeMsgQueueName = 'MessageConsumeOverTimeListener:msgQueue';
        while (true) {
            $this->getRedis();
            echo CoroutineManager::getInstance()->getCoroutineId() . "##@@@\r\n";
            $msgId = $this->connectionRedis->lpop($OverTimeMsgQueueName);
            echo $msgId . "\r\n";
            if ($msgId) {
                SwCoroutine::sleep(1); //处理业务时间
                echo "[" . date("Y-m-d H:i:s") . "] " . "Customer Process {$processIndex} Handle Message {$msgId}\r\n";
                $this->ConsumeOneOverMessage($msgId);
            } else {
                SwCoroutine::sleep(5);
            }

        }

    }

    public function consumeMessages($exchange, $queue, $routeKey, $callBack, bool $confirm = true, $consumer_tag = '')
    {
        $result = false;
        if (!$exchange || !$routeKey || !$queue) {
            return $result;
        }
        $this->getRabbit();
        $connection = $this->connectionRabbit;
        if ($confirm) {
            $channel = $connection->channel();
            $channel->confirm_select(); //confrim
        } else {
            $channel = $connection->channel();
        }

        // 3、声明一个交换器，并且设置相关属性
        $channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

        // 4、声明一个队列, 并且设置相关属性
        $channel->queue_declare($queue, false, true, false, false);

        // 5、通过路由键将交换器和队列绑定起来
        $channel->queue_bind($queue, $exchange, $routeKey);

        $channel->basic_consume(
            $queue,
            $consumer_tag,
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($callBack) {
                call_user_func($callBack, $message);
            }
        );
        // 8、一直阻塞消费数据
        while ($channel->is_consuming()) {
            $channel->wait();
        }

        function shutdown($channel, $connection)
        {
            $channel->close();
            $connection->close();
        }

        register_shutdown_function('shutdown', $channel, $connection);
    }


    public function produceMessage($messages, $exchange, $queue, $routeKey, bool $confirm = true, int $timeout = 5)
    {
        try {
            $result = false;
            if (!$messages || !$exchange || !$routeKey || !$queue) {
                return $result;
            }
            if (!is_array($messages)) {
                $messages = [$messages];
            }
            $this->getRabbit();
            $connection = $this->connectionRabbit;
            if ($confirm) {
                $channel = $connection->channel();
                $channel->confirm_select(); //confrim
            } else {
                $channel = $connection->channel();
            }
            $channel->set_ack_handler(function () use (&$result) {
                $result = true;
            });
            // 3、声明一个交换器，并且设置相关属性
            $channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);
            // 4、声明一个队列, 并且设置相关属性
            $channel->queue_declare($queue, false, true, false, false);
            // 5、通过路由键将交换器和队列绑定起来
            $channel->queue_bind($queue, $exchange, $routeKey);

            foreach ($messages as $message) {
                $message = new AMQPMessage($message, [
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                ]);
                $channel->basic_publish($message, $exchange, $routeKey);
            }
            $channel->wait_for_pending_acks_returns($timeout);

        } catch (\Throwable $exception) {
            throw $exception;
        } finally {
            //$this->putRabbit();
        }

        return $confirm ? $result : true;
    }


    public function confirmMsgToSend($msg_id, $flag): array
    {
        $res = ['status' => 0, 'result' => '确认投递失败'];
        try {
            $this->getRedis();
            if (!$this->connectionRedis) {
                return $res;
            }
            $exchangeName = 'tradeExchange';
            $routeKey = '/trade';
            $queueName = 'trade';
            $data = $this->connectionRedis->hget("message_system", (string)$msg_id);
            if (!empty($data)) {
                $data = json_decode($data, true);
                $data['status'] = 2;
                if ($flag == 2) {
                    //被消息恢复子系统投递的任务
                    $data['message_retries_number'] = $data['message_retries_number'] + 1;
                }
                $data = json_encode($data);
                //投递消息
                $messageRes = $this->produceMessage($data, $exchangeName, $queueName, $routeKey);
                var_dump($messageRes);
                //发布消息到交换机当中,并且绑定好路由关系
                if ($this->connectionRedis->hset("message_system", (string)$msg_id, $data) == 0 && $messageRes) {
                    //将消息投递给MQ(实时消息服务)
                    $res['status'] = 1;
                    $res['result'] = '确认并且投递成功';
                }
            }
            //$this->putRedis();
        } catch (\Exception $e) {
            $res['result'] = $e->getMessage();
        }
        return $res;
    }


    /**
     * 消息消费成功
     * @return array
     */
    public function ackMsg($msg_id): array
    {
        $this->getRedis();
        if (!$this->connectionRedis) {
            return ['status' => 0, 'result' => '任务消费失败'];
        }
        //删除已确认消费的消息
        $result = $this->connectionRedis->transaction(function ($redis) use ($msg_id) {
            $redis->hdel("message_system", (string)$msg_id);
            $redis->zrem("message_system_time", (string)$msg_id);
        });
        if ($result[0] !== false) {
            $data = ['status' => 1, 'result' => '任务消费成功'];
        } else {
            $data = ['status' => 0, 'result' => '任务消费失败'];
        }
        return $data;
    }


}