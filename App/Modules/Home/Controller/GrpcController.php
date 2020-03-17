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

    public function indexAction(HelloRequest $request)
    {
        $this->request=$request;
        $name=isset($_REQUEST['name']) ? $_REQUEST['name'] : $this->request->getName();
        try {
            if ($request) {
                $response_message = new HelloReply();
                $response_message->setMessage('Hello ' . $name);
                return $response_message;
            }
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }
}