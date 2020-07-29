<?php
/**
 * Created by PhpStorm.
 * User: dengh
 * Date: 2018/11/8
 * Time: 15:53
 */

namespace App\Modules\Home\Controller;

use App\Service\Util;
use App\Listener\SendSmsListener;
use App\Listener\SendEmailsListener;
use App\Listener\MessageConsumeOverTimeListener;
use App\Listener\ConfirmMessageConsumeListener;
use Framework\SwServer\Router\Annotation\Controller;
use App\Service\User;
use App\Service\Crypt;
use Framework\SwServer\Aop\PipelineAop;
use Framework\SwServer\Aop\ProceedingJoinPoint;
use Framework\SwServer\Pool\RabbitPoolManager;
use Framework\SwServer\Rpc\Protocol;
use Framework\Tool\Tool;
use Framework\SwServer\Task\TaskManager;
use Framework\Tool\PluginManager;
use Framework\SwServer\ServerManager;
use Framework\SwServer\Process\ProcessManager;
use Framework\SwServer\Coroutine\CoroutineManager;
use mysqli;
use PDO;
use Predis\Client;
use Swoole\Coroutine as co;
use Framework\SwServer\Pool\MysqlPoolManager;
use Framework\SwServer\Pool\RedisPoolManager;
use Framework\SwServer\Inotify\Daemon;
use Framework\SwServer\ServerController;
use Framework\SwServer\Event\Event;
use Framework\SwServer\Event\EventManager;
use Framework\SwServer\Annotation\AnnotationRegister;
use Framework\SwServer\Pool\DiPool;
use Swoole\Coroutine as SwCoroutine;
use App\Service\RabbitNotifyTransactionService;
use Swoole\Runtime;
use Framework\SwServer\Helper\Helper;
use Framework\SwServer\RateLimit\RateLimit;
use App\Service\Test;
use App\Dao\OrderDao;
use Framework\SwServer\Guzzle\ClientFactory;
use Framework\SwServer\Tracer\HttpClientFactory;
use Framework\SwServer\Tracer\TracerFactory;
use App\Middleware\TraceMiddleware;
use App\Handler\HttpHandler;
use Framework\Tool\Pipeline;
use App\Annotation\Bean;
use Framework\SwServer\Http\HttpJoinPoint;
use Framework\SwServer\Http\PipelineHttpHandleAop;
use App\Service\RabbitMqService;
use App\Service\RabbitTransactionService;
use App\Service\CommonService;
use App\Service\MessageService;
use Framework\SwServer\Pool\RpcClientPoolManager;
use App\Rpc\Contract\UserInterface;
use Framework\SwServer\Router\Annotation\RequestMapping;

/**
 * @Controller(prefix="site/index")
 */
class IndexController extends ServerController
{
    public $userService;
    public $util;
    private $event;
    public $tool;
    /**
     * @Bean(name="orderDao")
     */
    public $orderDao;

    public function init()
    {
        parent::init();

    }


    public function __construct(User $userService, Util $util)
    {
        $this->userService = $userService;
        $this->util = $util;
    }

    protected function testcoro()
    {
        $context = Co::getContext(); //携程中的上下文资源管理器，携程退出自动清理上下文资源
        $context["test"] = "haha1";
        $context["dd"] = "dd1";
        var_dump($context);
    }

    /**
     * @RequestMapping(path="delete", methods="get,post,put,delete")
     */
    public function destroyAction()
    {
        ServerManager::$eventManager->trigger("consulServiceDestroy");
        echo "Destroy Success";
    }

    private function initTracker()
    {
        $container = DiPool::getInstance();
        $container->setSingletonByObject(ClientFactory::class, new ClientFactory($container));
        $container->setSingletonByObject(HttpClientFactory::class, new HttpClientFactory($container->getSingleton(ClientFactory::class)));
        $container->setSingletonByObject(TracerFactory::class, new TracerFactory($container->getSingleton(HttpClientFactory::class)));
        $TracerFactory = $container->getSingleton(TracerFactory::class);
        $tracer = $TracerFactory->getTracer();
        $container->setSingletonByObject(TraceMiddleware::class, new TraceMiddleware($tracer));
    }

    public function getRabbit()
    {
        if ($this->connectionRabbit) {
            return $this->connectionRabbit;
        }
        $resourceData = RabbitPoolManager::getInstance()->get(0.1);
        if (!$resourceData) {
            throw new \Exception("Not Has Rabbit Pool Connection!!!");
        }
        defer(function () use ($resourceData) {
            RabbitPoolManager::getInstance()->put($resourceData);
        });
        $this->connectionRabbit = $resourceData;
    }

    private function sendMessage($msg_id)
    {
        $exchangeName = 'tradeExchange';
        $routeKey = '/trade';
        $queueName = 'trade';
        $connectionRedis = CommonService::getRedis();
        $data = $connectionRedis->hget("message_system", (string)$msg_id);
//        $messageService=DiPool::getInstance()->getSingleton(MessageService::class);
//       //投递消息
//        $messageRes = $messageService->produceMessage($data, $exchangeName, $queueName, $routeKey);
//         var_dump($messageRes);
    }

    protected function rpcClientDemo(){
        $rpcClient = CommonService::getRpcClient();
        try {
            $protocol = Protocol::new("2.0", UserInterface::class, 'getList', array(1, 'type'), []);
            $packet=$rpcClient->getPackage();
            $data = $packet->encode($protocol);
            $rpcClient->send($data);
        } catch (\Throwable $e) {
            print_r($e->getTrace());
        }
        $res = $rpcClient->recv();
        $responce = $packet->decodeResponse($res);
        print_r($responce);
        echo "Recv : " . $res . "\n";

    }

    /**
     * @RequestMapping(path="notify", methods="get,post,put,delete")
     */
    public function notifyAction(){
        if(mt_rand(1,2)==1){
            $content='success';
        }else{
            $content='fail';
        }

        echo $content;

    }


    /**
     * @RequestMapping(path="index/{id:\d+}", methods="get,post,put,delete")
     */
    public function indexAction(Tool $tool, Crypt $crypt, Event $e, SendSmsListener $smlistener, SendEmailsListener $semaillistener, MessageConsumeOverTimeListener $messageListener, $id)
    {
       // echo "9503---------\r\n";

        // echo "[" . date('Y-m-d H:i:s') . "] Current Use RedisPoolManager Connetction Look Nums:" . RedisPoolManager::getInstance()->getLength() . ",currentNum:" . RedisPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;
//        echo $id . "_\r\n";
        CommonService::setConfig(ServerManager::$config);
        CommonService::setRedis();
        CommonService::setRabbit();
        echo $this->request->fd."\r\n";
        $rb=DiPool::getInstance()->getSingleton(RabbitNotifyTransactionService::class);
        //for($i=1;$i<100;$i++){
           $res=$rb->order();
           print_r($res);
       // }

        //CommonService::setRpcClient();
        //CommonService::setMysql();
//        $mysql=CommonService::getMysql();
//        $rabbit = CommonService::getRabbit();
//        if ($rabbit->isConnected()) {
//            echo "rabbit connection!\r\n";
//        }
//        $redis=SwCoroutine::getContext(CoroutineManager::getInstance()->getCoroutineId())['redis'];
//        $aa=SwCoroutine::getContext();
//        print_r($aa['redis']);
//        print_r($redis);
//        print_r($mysql->table('user')->getConnection());
        echo CoroutineManager::getInstance()->getCoroutineId() . "_____\r\n";
//        $this->rpcClientDemo();


//        $msg_id = "13bf96280d29783e713fd9df786a5319rjpm8rhlmlbbohfres29tff25e";
//        $this->sendMessage($msg_id);


//        if(!$redis->exists("library")){
//            $redis->set("library",date("Y-m-d H:i:s"));
//        }
//        $id=$redis->get('library');
//        var_dump($id);

//        for($i=1;$i<=RabbitPoolManager::getInstance()->getLength();$i++){
//            $rabbitmq = RabbitPoolManager::getInstance()->get(0.1);
//        }
//
//        for($i=1;$i<=RedisPoolManager::getInstance()->getLength();$i++){
//            $redis = RedisPoolManager::getInstance()->get(0.1);
//        }
//
//        for($i=1;$i<=MysqlPoolManager::getInstance()->getLength();$i++){
//            $mysql = MysqlPoolManager::getInstance()->get(0.1);
//        }
//        $rabbitmq = RabbitPoolManager::getInstance()->get(0.1);
//        $mysql = MysqlPoolManager::getInstance()->get(0.1);
//        $pool=RedisPoolManager::getInstance();
//        echo "--------------------\r\n";
//        $redis = RedisPoolManager::getInstance()->get(0.1);
//        echo "[" . date('Y-m-d H:i:s') . "] Current Use RedisPoolManager Connetction Look Nums:" . RedisPoolManager::getInstance()->getLength() . ",currentNum:" . RedisPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;
//        $pool->put($redis);
//
//        echo "[" . date('Y-m-d H:i:s') . "] haha Current Use RedisPoolManager Connetction Look Nums:" . RedisPoolManager::getInstance()->getLength() . ",currentNum:" . RedisPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;

//        echo "[" . date('Y-m-d H:i:s') . "] Current Use RedisPoolManager Connetction Look Nums:" . RedisPoolManager::getInstance()->getLength() . ",currentNum:" . RedisPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;
//        defer(function () use ($redis,$pool) {
//            echo "##############collection#####################\r\n";
//            $pool->put($redis);
//
//            echo "[" . date('Y-m-d H:i:s') . "] haha Current Use RedisPoolManager Connetction Look Nums:" . $pool->getLength() . ",currentNum:" . $pool->getCurrentConnectionNums() . PHP_EOL;
//        });
        // var_dump($rabbitmq,$mysql,$redis);
//        if (!$rabbitmq) {
//            throw new \Exception("Not Has Rabbit Pool Connection!!!");
//        }
//        $info = CoroutineManager::set('rabbitmq', Co::getuid()); // get context of this coroutine
////        defer(function () use ($rabbitmq) {
////            RabbitPoolManager::getInstance()->put($rabbitmq);
////        });
//        $id=CoroutineManager::get('info');
//        var_dump($info,$id);
//        if(ServerManager::isTaskProcess()){
//             echo 'task process'."\r\n";
//        }else{
//            echo 'worker process'."\r\n";
//        }
//        $key = 'queue';
//        $value = uniqid();
//        $redis = CommonService::getRedis();
//        $redis->rpush($key, $value);
//
//          $rb=DiPool::getInstance()->getSingleton(RabbitTransactionService::class);
//        for($i=1;$i<100;$i++){
//           $rb->order();
//        }

        // echo "[" . date('Y-m-d H:i:s') . "] Current Use RedisPoolManager Connetction Look Nums:" . RedisPoolManager::getInstance()->getLength() . ",currentNum:" . RedisPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;

        // echo "[" . date('Y-m-d H:i:s') . "] Current Use Rabbitmq Connetction Look Nums:" . RabbitPoolManager::getInstance()->getLength() . ",currentNum:" . RabbitPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;

//        $exchange = 'test_exchange_confirm';
//        $queue = 'test_queue_confirm';
//        $route_key = 'test_confirm';
//        $message="hello world!!!";
//        $consumer_tag='consumer_tag';
//        $res=RabbitMqService::produceMessage($message,$exchange,$queue,$route_key);
//        var_dump($res);

        //RabbitMqService::consumeMessage();
//        $daos=DiPool::getInstance()->getDaos();
//                    try {
//                $resourceData =RabbitPoolManager::getInstance()->get(0.1);
//                if ($resourceData) {
//
//                    print_r($resourceData);
//
//                    RabbitPoolManager::getInstance()->put($resourceData);
//
//                    //\Swoole\Coroutine::sleep(4); //sleep 10秒,模拟耗时操作
//
//                }
//                echo "[" . date('Y-m-d H:i:s') . "] Current Use Rabbitmq Connetction Look Nums:" . RabbitPoolManager::getInstance()->getLength() . ",currentNum:" . RabbitPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;
//
//            } catch (\Exception $e) {
//                echo "@@@@@@@@@@@@@@@@@@@@\r\n";
//                echo $e->getMessage();
//            }
//        $list=CoroutineManager::getInstance()->listCoroutines();
//       // print_r($list);
//        echo CoroutineManager::getInstance()->getCoroutineId()." --------\r\n";
//        $request=CoroutineManager::get('tracer.request');
//
//        $container=DiPool::getInstance();
//        $this->initTracker();
//        //$handler=new HttpHandler($this->response);
//        $TraceMiddleware=$container->getSingleton(TraceMiddleware::class);
//        $response=$TraceMiddleware->process();

//
//        $pipes1=[$TraceMiddleware];
//        //$pipes=[new Handler1(),new Handler2(),new Handler3(),new Handler4()];
//
//
//        $res= (new Pipeline())->via('process')->send($request)->through($pipes1)->then(function ($post) {
//            return $post;
//        }); // 执行输出为 2
//
//
//
//
//
//        $responce=$TraceMiddleware->process($request,$handler);
//
//        print_r($responce);

        // print_r(DiPool::getInstance()->getSingletons());

        // print_r($request);

//        $orderDao=ServerManager::getApp()->orderDao;
//        print_r($orderDao);
//        print_r($orderDao->getUserService());


        //print_r(AnnotationRegister::getInstance()->getAspectAnnotations());
        //$orderdaoclassname=OrderDao::class;
        //$orderDao=ServerManager::getApp()->$orderdaoclassname;
        //$orderDao->createUser([11,22,33]);
//        $object=DiPool::getInstance()->register(Test::class); //向容器内注册对象
//        echo "-------------------------------------\r\n";
//        print_r($object);
        // print_r(DiPool::getInstance()->getSingletons());
//        echo "#######################################\r\n";
//        print_r(DiPool::getInstance()->get(Test::class));

//        var_dump(IndexController::class);
////        print_r(ServerManager::$app);
//        print_r(ServerManager::getApp());
//        var_dump(ServerManager::getApp()->request->get);
//        print_r($this->httpInput);
//        print_r($this->httpInput->getAllGet());
//        print_r($this->httpInput->postGet("test"));;

//        $res = RateLimit::getInstance()->minLimit('indexAction', function () {
//            echo "Rate Limit:" . date("Y-m-d H:i:s") . "\r\n";
//        }); //方法控制限流
//        if (!$res['flag']) {
//            throw  new \Exception($res['msg'] . "\r\n");
//        }

//        go(function () {
//
//             echo "this is go function\r\n";
//        });

        //$serviceId = Helper::registerService('swoft',"192.168.99.88",9501);
        //Helper::removeService($serviceId);
        //var_dump($serviceId);
        // $response = Agent::getInstance()->deregisterService($serviceId);
        // print_r($response);
//        $context = Co::getContext();
//        $context["test"] = "haha";
//        $context["dd"] = "dd";
//        $this->testcoro();
//        $this->tool = $tool;
//        var_dump($context["test"], $context["dd"]);
//
//        try {
//            $resourceData = RedisPoolManager::getInstance()->get(5);
//            if ($resourceData) {
//                $result = $resourceData['resource']->set('name', 'tayue');
//                $result1 = $resourceData['resource']->get('library');
//                $result2 = $resourceData['resource']->get('name');
//                var_dump($result1, $result2);
//                //\Swoole\Coroutine::sleep(4);
//                defer(function () use($resourceData) {
//                    RedisPoolManager::getInstance()->put($resourceData);
//                    echo "[" . date('Y-m-d H:i:s') . "] Current Use Redis Connetction Look Nums:" . RedisPoolManager::getInstance()->getLength() . ",currentNum:" . RedisPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;
//                });
//
//            }
//
//
//        } catch (\Exception $e) {
//            echo $e->getMessage();
//        }


//            $db = new \mysqli;
//            $db->connect('127.0.0.1', 'root', 'root', 'test');
//
//            $result = $db->query("show databases");
//            var_dump($result->fetch_all());
//
//            $db = new \PDO("mysql:host=127.0.0.1;dbname=test;charset=utf8", "root", "root");
//            $query = $db->prepare("select * from userinfo where id=?");
//            $rs = $query->execute(array(1));
//            var_dump($rs);
//            echo count($query->fetchAll());
//
//            echo '------------------------------------';

        //$em=ServerManager::getApp()->eventmanager;
//        $services=DiPool::getInstance()->getServices();
//        print_r($services);
//        $comments=DiPool::getInstance()->getComponents();


        //$em->attach("consulRegister","App\Listener\SendSmsListener");

//        ServerManager::$eventManager->addListener($smlistener,["createorder"=>1]);
//        ServerManager::$eventManager->addListener($semaillistener,["createorder"=>2]);
//        //print_r(ServerManager::$eventManager);
//        ServerManager::$eventManager->trigger("createorder",null,['test','test1']);
        //ServerManager::$eventManager->attach("consulRegister","App\Listener\SendSmsListener");->trigger("consulRegister",null,['test','test1']);
//        $context = new Co\Context(); //swoole 协程上下文管理器注册上下文环境后协程执行完成后自动回收
//        $context['crypt'] = $crypt;
//
//
//       // $userService->display();
//
//
//        $crypt->display();
//
        // $this->userService->display();
//        //Runtime::enableStrictMode();
//
        //ServerManager::$isEnableRuntimeCoroutine=false;
        //从池子中获取一个实例
//            try {
//                $resourceData = MysqlPoolManager::getInstance()->get(0.1);
//                if ($resourceData) {
//                    //print_r($resourceData);
//                    MysqlPoolManager::getInstance()->put($resourceData);
//                    //print_r($resourceData);
//                    $result = $resourceData['resource']->query("select * from user");
//                    echo $resourceData['resource']->getLastSql()."__\r\n";
//
//                    //\Swoole\Coroutine::sleep(4); //sleep 10秒,模拟耗时操作
//
//                    echo json_encode($result);
//                }
//                //echo "[" . date('Y-m-d H:i:s') . "] Current Use Mysql Connetction Look Nums:" . MysqlPoolManager::getInstance()->getLength() . ",currentNum:" . MysqlPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;
//
//            } catch (\Exception $e) {
//                echo "@@@@@@@@@@@@@@@@@@@@\r\n";
//                echo $e->getMessage();
//            }

        // $userData1 = ServerManager::getApp()->userService->findUser();
//       // $userData2 = ServerManager::getApp()->userService->findUser();
//        $userData1 = $this->userService->findUser();
////        $userData2=$this->userService->findUser();
//
//        // print_r(ServerManager::getApp('cid_4'));
//
        // print_r($userData1);
//        $components=DiPool::getInstance()->getComponents();
//        print_r($components);
//        print_r(ServerManager::getApp()->view);
//        ServerManager::getApp()->view->assign('name', 'Http Server  sssss !!!');
//        ServerManager::getApp()->view->display('index.html');

    }

    /**
     * @RequestMapping(path="index/handle", methods="get,post,put,delete")
     */
    public function handleAction(MessageConsumeOverTimeListener $messageListener, ConfirmMessageConsumeListener $confirmMessageConsumeListener)
    {
//        CommonService::setRedis();
//        CommonService::setRabbit();
        ServerManager::$eventManager->addListener($messageListener, ["order" => 1]);
        ServerManager::$eventManager->addListener($confirmMessageConsumeListener, ["order" => 2]);

        //print_r(ServerManager::$eventManager);
        ServerManager::$eventManager->trigger("order", null, []);
    }


    public function packAction()
    {
        $len = 10;
        $data = ['code' => 200, 'data' => "hello world !!!"];
        $body = self::encode($data, 1);
        $header = ['length' => 'N', 'name' => 'a30'];
        $bin_header_data = '';
        foreach ($header as $key => $value) {
            if (isset($header[$key])) {
                // 计算包体长度
                if ($key == 'length') {
                    $bin_header_data .= pack($value, strlen($body));

                } else {
                    // 其他的包头
                    $bin_header_data .= pack($value, $header[$key]);
                }
            }
        }

        $resData = $bin_header_data . $body;
        $unpack_length_type = '';
        if ($header) {
            foreach ($header as $key => $value) {
                $unpack_length_type .= ($value . $key) . '/';
            }
        }
        $unpack_length_type = trim($unpack_length_type, '/');
        $header = unpack($unpack_length_type, mb_strcut($resData, 0, 45, 'UTF-8'));
        $pack_body = mb_strcut($resData, 45, null, 'UTF-8');

        var_dump($header, $pack_body);


    }

    /**
     * encode 数据序列化
     * @param mixed $data
     * @param int $serialize_type
     * @return  string
     */

    public static function encode($data, $serialize_type = 1)
    {

        switch ($serialize_type) {
            // json
            case 1:
                return json_encode($data);
            // serialize
            case 2:
                return serialize($data);
            case 3;
            default:
                // swoole
                return \Swoole\Serialize::pack($data);
        }
    }


    public
    function indexsAction()
    {
        $context = Co::getContext();
        var_dump($context);
        print_r(ServerManager::getApp());
        go(function () {
            //从池子中获取一个实例
            try {
                $resourceData = MysqlPoolManager::getInstance()->get(5);
                if ($resourceData) {
                    $result = $resourceData->query("select * from user");
                    print_r($result);
                    //\Swoole\Coroutine::sleep(4); //sleep 10秒,模拟耗时操作
                    MysqlPoolManager::getInstance()->put($resourceData);
                }
                echo "[" . date('Y-m-d H:i:s') . "] Current Use Mysql Connetction Look Nums:" . MysqlPoolManager::getInstance()->getLength() . ",currentNum:" . MysqlPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;

            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        });
//
//
//        go(function () {
//            //从池子中获取一个实例
        try {
            $resourceData = RedisPoolManager::getInstance()->get(5);
            print_r($resourceData);
            if ($resourceData) {
                $result = $resourceData->set('name', 'tayue');
                //$result1 = $resourceData->get('name');
                // print_r($result1);
                //\Swoole\Coroutine::sleep(4);
                //RedisPoolManager::getInstance()->put($resourceData);
            }
            echo "[" . date('Y-m-d H:i:s') . "] Current Use Redis Connetction Look Nums:" . RedisPoolManager::getInstance()->getLength() . ",currentNum:" . RedisPoolManager::getInstance()->getCurrentConnectionNums() . PHP_EOL;

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
//        });

        $services = DiPool::getInstance()->getServices();
        $comments = DiPool::getInstance()->getComponents();
        $this->util->display();
        echo ServerManager::getApp()->userService->display() . "_____________######\r\n";
        // $a=new MysqlPoolManager(array());
        // var_dump($a);
//       $cid= CoroutineManager::getInstance()->getCoroutineId();
//
        //   $cid1 = CoroutineManager::getInstance()->getCoroutineId();
//        var_dump($cid,$cid1);
        // 当前协程id
//        $cid1 = $cid2 = $ss = '';
//        $cid = CoroutineManager::id();
//        CoroutineManager::create(function () use (&$ss, &$cid, &$cid1, &$cid2) {
//            $cid1 = CoroutineManager::id();
//            echo "$cid=>$cid1\r\n";
//            CoroutineManager::create(function () use (&$ss, &$cid, &$cid1, &$cid2) {
//
//                $cid2 = CoroutineManager::id();
//                $ss = "$cid=>$cid1=>$cid2\r\n";
//                echo $ss . "\r\n";
//            });
//        });
//
//        var_dump($cid, $cid1, $cid2, $ss);
//        // 当前运行上下文ID, 协程环境中，顶层协程ID; 任务中，当前任务taskid; 自定义进程中，当前进程ID(pid)
//        $tid = CoroutineManager::tid();
//        //echo "{$cid1}##\r\n";
//        echo "tid:{$tid}\r\n";

//        $res=CoroutineManager::getIdMap();
//        var_dump($res);

//        $pid=$_GET['pid'];
//        $pa=ProcessManager::getInstance()->getProcessByPid($pid);
//        ProcessManager::getInstance()->writeByProcessName('CronRunner','hello CronRunner');
//
//        var_dump($pa);
//        PluginManager::getInstance()->registerFuncHook('ProcessAsyncTaskFunc',function ($a,$b){
//            return $a+$b;
//        });
//
//        PluginManager::getInstance()->triggerHook('ProcessAsyncTask',9,4);
//        echo $a;
//       new \App\Modules\Home\Controller\sss();
//
//         var_dump(ServerManager::$app);
        // $this->echo2br("App\\Modules\\Home\\Controller\\IndexController\\indexsAction\r\n");


    }

    public
    function taskAction()
    {

        // $res=PluginManager::getInstance()->getListeners();
        //print_r($res);
        $time = date("Y-m-d H:i:s");

        // $this->echo2br("asyncTaskId:{$taskId} Finished!\r\n");
        $a = 111;
        $b = 2;
        $c = 3;
        //$taskId=TaskManager::asyncTask(["Server/Task/TestTask","asyncTaskTest"],5,$a,$b,$c);
        // $taskId=TaskManager::asyncTask(["Server/Task/TestTask","asyncTaskTest"],5,$a,$b,$c);
        $taskId1 = TaskManager::coTask(["Server/Task/TestTask", "asyncTaskTest"], 2, $a, $b, $c);
        var_dump($taskId1);
        TaskManager::processAsyncTask(["Server/Task/TestTask", "asyncTaskTest"], $a, $b, $c);
        // $taskId=TaskManager::syncTask(["Server/Task/TestTask","syncTaskTest"],[$time],13);
        $this->echo2br("syncTaskId:{$taskId1} Finished!\r\n");
    }

    public
    function dateAction()
    {
        echo date("Y-m-d H:i:s");
    }

    public
    function ddAction()
    {
//        $s = new \SphinxClient;
//        $s->setServer("localhost", 9312);
//        $s->setMatchMode(SPH_MATCH_ANY);
//        $s->setMaxQueryTime(3);
//
//        $result = $s->query("test");

        $sphinx = new \SphinxClient;
        $sphinx->setServer("localhost", 9312);

        $sphinx->SetArrayResult(true);
        $sphinx->SetMatchMode(SPH_MATCH_EXTENDED2);
        $sphinx->SetSelect("*");
        $sphinx->ResetFilters();
        //$sphinx->SetFilter('product_id', array(14001949));
        $query = " @amazon_item_name 'Universal'"; //@amazon_item_name  备注（amazon_item_name） 是索引列的字段
        $result = $sphinx->query($query, "blog");    //星号为所有索引源
        var_dump($result);
        echo '<pre>';
        print_r($result);
        $count = $result['total'];        //查到的结果条数
        $time = $result['time'];            //耗时
        $arr = $result['matches'];        //结果集
        $id = '';
        for ($i = 0; $i < $count; $i++) {
            $id .= $arr[$i]['id'] . ',';
        }
        $id = substr($id, 0, -1);            //结果集的id字符串


        echo '<pre>';
        print_r($arr);
        echo $id;
    }


    public
    function __beforeAction()
    {
        $this->echo2br("__beforeAction\r\n");
    }

    public
    function __afterAction()
    {
        $this->echo2br("__afterAction\r\n");
    }


    protected
    function echo2br($str)
    {
        echo nl2br($str);
    }

    public function corotest()
    {

    }

    public function postAction()
    {
        print_r($_REQUEST);
        echo "Register Success !";
    }
}
