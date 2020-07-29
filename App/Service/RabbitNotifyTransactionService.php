<?php


namespace App\Service;

/*
 * Class RabbitNotifyTransactionService
 * @package App\Service

    分布式事务最大努力通知型

    实现
    业务活动的主动方，在完成业务处理之后，向业务活动的被动方发送消息，被动方需主动响应正确消息,否则根据定时策略,最大努力通知。

    业务活动的被动方也可以向业务活动主动方查询，恢复丢失的业务消息。

    约束
    被动方的处理结果不影响主动方的处理结果

    成本
    业务查询与校对系统的建设成本

    适用范围
    对业务最终一致性的时间敏感度低
    跨企业的业务活动

    方案特点
    业务活动的主动方在完成业务处理后，向业务活动被动方发送通知消息（允许消息丢失）
    主动方可以设置时间阶梯型通知规则，在通知失败后按规则重复通知，直到通知N次后不成功主动方提供校对查询接口给被动方按需校对查询，用于恢复丢失的业务消息

    应用案例
    银行通知、商户通知等（各大交易业务平台间的商户通知：多次通知、查询校对、对账文件）

    Rabbitmq延迟队列实现原理： 死信队列
    由于rabbitmq本身并没有提供延迟队列的功能，所以需要借助

    1、rabbitmq 可以针对 Queue和Message 设置 x-message-ttl 来控制消息的生存时间，如果超时，消息变为 dead letter（死信）
    2、rabbitmq 的queue 可以配置 x-dead-letter-exchange 和 x-dead-letter-routing(可选)
    两个参数，来控制队列出现 dead letter 的时候，重新发送消息的目的地


 */

class RabbitNotifyTransactionService
{
    public $messageService; //消息服务子系统（包含接口）
    public $orderService;

    public function __construct()
    {
        $this->messageService = new MessageService();
        $this->orderService = new OrderService();
    }
    //主动方信息发送
    public function order()
    {
        //预发送消息（消息状态子系统）

        $data=[
            'msg_id'=>uniqid(),
            'version'=>1,
            'create_time'=>time(),
            'message_body'=>['order_id'=>uniqid(),'shop_id'=>2],
            'notify_url'=>'http://192.168.99.88:9501/site/index/notify',//通知地址
            'notify_rule'=>[1=>5,2=>10,3=>15],//单位为秒
            'notify_retries_number'=>0, //重试次数，
            'default_delay_time'=>1,//毫秒为单位
            'status'=>1, //消息状态
        ];
        $res=$this->messageService->delayPublish($data);

        return $res;
    }

}