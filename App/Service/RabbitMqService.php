<?php


namespace App\Service;

use Framework\SwServer\Pool\RabbitPoolManager;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMqService
{
    private static $rabbitConnection=null;

    public static function consumeMessage($exchange, $queue, $routeKey, $consumer_tag, $callBack, bool $confirm = true, string $processName = '')
    {
        $result = false;
        if (!$exchange || !$routeKey || !$queue) {
            return $result;
        }
        if ($processName) {
            echo "Process:{$processName},Start Consume Message...\r\n";
        } else {
            echo "Start Consume Message...\r\n";
        }
        $connection = self::getConnection();
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
    }


    public static function produceMessage($messages, $exchange, $queue, $routeKey, bool $confirm = true, int $timeout = 5)
    {
        try {
            $result = false;
            if (!$messages || !$exchange || !$routeKey || !$queue) {
                return $result;
            }
            if (!is_array($messages)) {
                $messages = [$messages];
            }
            $connection = self::getConnection();
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
        }finally{
            RabbitPoolManager::getInstance()->put($connection);
        }
        return $confirm ? $result : true;
    }

    public static function getConnection(int $timeout = 4)
    {
        $connectionChannelData = CommonService::getRabbit();
        if (!$connectionChannelData) {
            throw new Exception("not has Pool Connection!!!");
        }
        $connection = $connectionChannelData;
        if (!$connection || !($connection instanceof AMQPStreamConnection)) {
            throw new Exception("not has Pool Connection!!!");
        }
        self::$rabbitConnection = $connection;
        return self::$rabbitConnection;
    }

    public function getConfirmChannel(): AMQPChannel
    {
        if (!$this->confirmChannel || !$this->check()) {
            $this->confirmChannel = $this->getConnection()->channel();
            $this->confirmChannel->confirm_select();
        }
        return $this->confirmChannel;
    }

    public function check(): bool
    {
        return isset($this->connection) && $this->connection instanceof AbstractConnection && $this->connection->isConnected() && !$this->isHeartbeatTimeout();
    }


    public function consumeCallback(AMQPMessage $message)
    {
        /** @var AMQPChannel $channel */
        $channel = $message->delivery_info['channel'];
        $deliveryTag = $message->delivery_info['delivery_tag'];
        echo 'start do something!' . "\r\n";
        sleep(2);
        echo 'end do something!' . "\r\n";
        $channel->basic_ack($deliveryTag);
        echo '[' . date('Y-m-d H:i:s') . "] Confirm Message:" . $message->body . "\r\n";
    }
}