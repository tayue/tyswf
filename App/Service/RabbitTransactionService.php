<?php


namespace App\Service;

use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\SwServer\Pool\RedisPoolManager;
use Framework\SwServer\Pool\RabbitPoolManager;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

/*
 *      基于Rabbitmq 实现分布式事务 （最终一致性）
 *
        消息子系统的构建
        1.构建消息服务子系统（包含接口）
        存储预发送消息（主动方应用系统）
        确认并发送消息（主动方应用系统）
        确认消息已被成功消费（被动方应用系统）
        查询状态确认超时的消息（消息状态确认子系统）
        查询消费确认超时的消息（消息恢复子系统）

        2.构建其他服务（骨架大搭建）
        1.订单服务
        2.积分服务
        3.消息子系统
        4.网关服务

        3.简单的封装rabbitMQ的连接池
        1.解决连接池，无法取出连接的问题

        4、消息发布确认
        5、消息存储的设计（任务数据存储）
        version 版本号
        create_time 创建时间
        message_id 消息ID
        message_body 消息内容
        consumer_queue 消费队列
        message_retries_number 消息重发次数
        dead 是否死亡
        status 状态(预发送、发送中、已消费、已死亡)

        6、消息id的生成
        7、服务提供任务执行结果查询接口
        整体流程：
        1、用户下单，主动方应用预发送消息给消息服务子系统。
        2、消息服务子系统存储预发送的消息。
        3、返回存储预发送消息的结果。
        4、如果第3步返回的结果是成功的，则执行业务操作，否则不执行。
        5、业务操作成功后，调用消息服务子系统进行确认发送消息。
        6、将消息服务库中存储的预发送消息发送，并更新该消息的状态为已发送（但不是已被消费）。
        7、消息中间件发送消息到消费端应用。
        8、消费端应用调用被动方应用服务。
        9、被动方应用返回结果给消费端应用。
        10、消费端应用向消息中间件ack此条消息，并向消息服务子系统进行确认成功消费消息，11、让消息服务子系统删除该条消息或者将状态置为已成功消费。
        12、消息状态子系统定时去查一下消息数据，看看有没有是已发送状态的超时消息，就是一直没有变成已成功消费的那种消息，主动方应用系统应该提供查询接口，针对某条消息查询该条消息对应的业务数据是否为处理成功
        13、如果业务数据是处理成功的状态，那么就再次调用确认并发送消息，即进入第6步。
        14、如果业务数据是处理失败的，那么就调用消息服务子系统进行删除该条消息数据。

 *
 */

//主动方
class RabbitTransactionService
{
    public $messageService; //消息服务子系统（包含接口）
    public $orderService;

    public function __construct()
    {
        $this->messageService = new MessageService();
        $this->orderService = new OrderService();
    }

    public function uuid() {
        if (function_exists ( 'com_create_guid' )) {
            return com_create_guid ();
        } else {
            mt_srand ( ( double ) microtime () * 10000 ); //optional for php 4.2.0 and up.随便数播种，4.2.0以后不需要了。
            $charid = strtoupper ( md5 ( uniqid ( rand (), true ) ) ); //根据当前时间（微秒计）生成唯一id.
            $hyphen = chr ( 45 ); // "-"
            $uuid = '' . //chr(123)// "{"
                substr ( $charid, 0, 8 ) . $hyphen . substr ( $charid, 8, 4 ) . $hyphen . substr ( $charid, 12, 4 ) . $hyphen . substr ( $charid, 16, 4 ) . $hyphen . substr ( $charid, 20, 12 );
            //.chr(125);// "}"
            return $uuid;
        }
    }

    //主动方发送业务
    public function order()
    {
       //预发送消息（消息状态子系统）
        $msg_id = $this->uuid();
        $data = [
            'msg_id' => uniqid(),
            'version' => 1,
            'create_time' => time(),
            'message_body' => ['order_id' => 12133, 'shop_id' => 2],
            'notify_url' => 'http://127.0.0.1:9804/notify/index',//通知地址
            'notify_rule' => [1 => 5, 2 => 10, 3 => 15],//单位为秒
            'notify_retries_number' => 0, //重试次数，
            'default_delay_time' => 1,//毫秒为单位
            'status' => 1, //消息状态
        ];
//        var_dump($this->notifyService->publish($data));
//        return ['1'];

        $order_id = md5(uniqid());
        $prepareMsgData = [
            'msg_id' => $msg_id,
            'version' => 1,
            'create_time' => time(),
            'message_body' => ['order_id' => $order_id, 'shop_id' => 2],
            'consumer_queue' => 'order', //消费队列（消费者）
            'message_retries_number' => 0, //重试次数，
            'status' => 1, //消息状态
        ];

        //预存储消息
        $result = $this->messageService->prepareMsg($prepareMsgData);
        $data = [
            'order_id' => $order_id,
            'msg_id' => $msg_id
        ];
        //调用订单服务更新状态
        if ($result['status'] == 1) {  //消息恢复子系统（查询未确认消息）          确认并且投递
            $this->orderService->update($data)['status'] == 1 && $this->messageService->confirmMsgToSend($msg_id, 1);//更新订单
        }
        //确认并且投递消息
        return [$result];
    }

}