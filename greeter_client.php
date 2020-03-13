<?php

require_once __DIR__ . '/vendor/autoload.php';
ini_set("display_errors", "On");//打开错误提示
ini_set("error_reporting", E_ALL);//显示所有错误
use App\Protobuf\GreeterClient;
use App\Protobuf\HelloRequest;
use App\Protobuf\HelloReply;
use Google\Protobuf\Internal\Message;
use Framework\SwServer\Grpc\Parser;

$name = !empty($argv[1]) ? $argv[1] : 'Swoole';


go(function () use ($name) {
    try {
        $opt = ['package_max_length' => 9000000];
        $greeterClient = new GreeterClient('192.168.99.88:9501', $opt);
        $greeterClient->start();
        $request = new HelloRequest();
        $request->setName($name);
        list($reply, $status,$response) = $greeterClient->SayHello1($request);
          print_r($response);
        if($reply instanceof Message){
            $message = $reply->getMessage();
            echo "success {$message}\r\n";
        }else if(is_string($reply)){
            echo "{$reply}==>{$status}\r\n";
        }


        // sleep(3);
        $greeterClient->close();
    } catch (Throwable $e) {
        echo $e->getMessage();
    }
});

//go(function () {
//    $domain = 'www.zhihu.com';
//    $cli = new Swoole\Coroutine\Http2\Client($domain, 443, true);
//    $cli->set([
//        'timeout' => -1,
//        'ssl_host_name' => $domain
//    ]);
//    $cli->connect();
//    $req = new swoole_http2_request;
//    $req->method = 'POST';
//    $req->path = '/api/v4/answers/300000000/voters';
//    $req->headers = [
//        'host' => $domain,
//        "user-agent" => 'Chrome/49.0.2587.3',
//        'accept' => 'text/html,application/xhtml+xml,application/xml',
//        'accept-encoding' => 'gzip'
//    ];
//    $req->data = '{"type":"up"}';
//    $cli->send($req);
//    $response = $cli->recv();
//    assert(json_decode($response->data)->error->code === 602);
//});


//    list($reply, $status) = $greeterClient->SayHello($request);
//    var_dump($reply);
//    $message = $reply->getMessage();
//    echo "{$message}\n";
//    $greeterClient->close();

