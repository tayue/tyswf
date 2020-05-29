<?php


namespace App\Modules\Home\Controller;
use App\Dao\OrderDao;
use App\Protobuf\GreeterClient;
use App\Protobuf\HelloReply;
use App\Protobuf\HelloRequest;
use Framework\SwServer\Annotation\AnnotationRegister;
use Framework\SwServer\Pool\DiPool;
use Framework\SwServer\ServerManager;
use Framework\SwServer\ServerController;
class TestController  extends ServerController
{
   public function indexAction(){
       echo "indexAction";
       $className = OrderDao::class;
       $methodName = "createUser";
       if (AnnotationRegister::checkIsHasAspectAnnotation($className, $methodName)) {
           $proxyClassName = $className . "Aop";
           $dis=DiPool::getInstance()->getSingletons();
           print_r($dis);
           $dao = DiPool::getInstance()->get($proxyClassName);

           $orderData = array(1, 2, 4);
           echo $res = $dao->$methodName($orderData);

       }
   }

    public function grpcAction(HelloRequest $request)
    {
        echo "grpc demo\r\n";
        $this->request=$request;
        $name=isset($_REQUEST['name']) ? $_REQUEST['name'] : "test";
        try {
            $opt = ['package_max_length' => 9000000];
            $greeterClient = new GreeterClient('192.168.99.88:50051', $opt);  //这里的grpc客户端用于数据流的传输
            $greeterClient->start();

            $this->request->setName($name);
            list($reply, $status,$response) = $greeterClient->SayHello($this->request);
            var_dump($reply, $status,$response);
            if($reply instanceof HelloReply){
                $message = $reply->getMessage();
                echo "{$message}--------\n";
            }else{
                echo "faild\r\n";
            }
            //$greeterClient->close();
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }

    public function ajaxAction()
    {
     echo json_encode([["name"=>'tayue','age'=>30],["name"=>'zhangsan','age'=>31]]);
    }

    public function postAction()
    {
         file_put_contents("./post.txt",var_export(ServerManager::getApp()->request->post,true),FILE_APPEND);
    }
}