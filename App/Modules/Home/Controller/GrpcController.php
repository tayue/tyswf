<?php


namespace App\Modules\Home\Controller;
use App\Protobuf\HelloReply;
use App\Protobuf\HelloRequest;
use Framework\SwServer\Grpc\Parser;
use Framework\SwServer\ServerManager;
use Framework\SwServer\ServerController;
use App\Protobuf\GreeterClient;

class GrpcController extends ServerController
{
    public $request;


    public function grpcAction(HelloRequest $request,$a)
    {
        $name=isset($_REQUEST['name']) ? $_REQUEST['name'] : "test";
        try {
            if ($request) {
                $name= $name ? $name : $request->getName();
                $response_message = new HelloReply();
                $response_message->setMessage('Hello ' . $name);
                return $response_message;


            }
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }
}