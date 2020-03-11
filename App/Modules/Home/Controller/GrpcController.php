<?php


namespace App\Modules\Home\Controller;
use App\Protobuf\HelloReply;
use App\Protobuf\HelloRequest;
use Framework\SwServer\ServerManager;
use Framework\SwServer\ServerController;
use App\Protobuf\GreeterClient;

class GrpcController extends ServerController
{
    public function indexAction()
    {
        $name=$_REQUEST['name'];
        try {
            $opt = ['package_max_length' => 9000000];
            $greeterClient = new GreeterClient('192.168.99.88:50051', $opt);  //这里的grpc客户端用于数据流的传输
            $greeterClient->start();
            $request = new HelloRequest();
            $request->setName($name);
            list($reply, $status,$response) = $greeterClient->SayHello($request);
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
}