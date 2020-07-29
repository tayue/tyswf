<?php

ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);

require_once './vendor/autoload.php';

use App\Service\UserService;
use Framework\SwServer\Rpc\Packet\JsonPacket;
use Framework\SwServer\Rpc\Protocol;
use App\Rpc\Service\RpcUserService;
use App\Rpc\Contract\UserInterface;

function coroClient()
{
    go(function () {
        $client = new Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
        if (!$client->connect('192.168.99.88', 8888)) {
            exit("connect failed\n");
        }
        $packet = new JsonPacket();
        try {
            $protocol = Protocol::new("2.0", UserInterface::class, 'getList', array(1, 'type'), []);
            $data = $packet->encode($protocol);
            $client->send($data);
        } catch (\Throwable $e) {
            print_r($e->getTrace());
        }
        $res = $client->recv();
        $responce = $packet->decodeResponse($res);
        print_r($responce);
        echo "Recv : " . $res . "\n";
    });
}

coroClient();return;

$client = new \swoole_client(SWOOLE_SOCK_TCP);
if (!$client->connect('192.168.99.88', 8888)) {
    exit("connect failed\n");
}

for ($l = 0; $l < 1; $l++) {
    $data = '';
    for ($i = 0; $i < 1; $i++) {
//        $len = rand(10000, 20000);
//        echo "package length=" . ($len + 4) . "\n";
        rpcclient($client);
    }
    //echo 'total send size:', strlen($data),"\n";
    //$client->send($data);

}

function rpcclient(&$client)
{

    try {
        $packet = new JsonPacket();
        $protocol = Protocol::new("1.0", UserInterface::class, 'getList', array(12, 'type2'), []);
        $data = $packet->encode($protocol);
        $client->send($data);
    } catch (\Throwable $e) {
        print_r($e->getTrace());
    }
    $res = $client->recv();
    // $responce = $packet->decodeResponse($res);
    // print_r($responce);
    echo "Recv #{$client->sock}: " . $res . "\n";
}


die("ff");

function send_test3($client, $len)
{
    $data = pack('N', $len + 4);
    var_dump($data);
    $data .= str_repeat('A', $len) . rand(1000, 9999);
    $chunks = str_split($data, 4000);
    echo count($chunks) . "\r\n";
    foreach ($chunks as $ch) {
        $client->send($ch);
    }
    echo "package: " . substr($data, -4, 4) . "\n";
}

function send_test2($client, $len)
{
    $data = pack('N', $len + 4);
    $data .= str_repeat('A', $len) . rand(1000, 9999);
    $client->send($data);
}

function send_test1($client, $len)
{
    $client->send(pack('N', $len + 4));
    usleep(10);
    $data = str_repeat('A', $len) . rand(1000, 9999);
    $client->send($data);
    echo "package: " . substr($data, -4, 4) . "\n";
}

function send_tests($client, $len)
{
    $client->send(pack('N', $len + 4));
    usleep(10);
    $data = str_repeat('B', $len) . rand(1000, 9999);
    $client->send($data);
    echo "package: " . substr($data, -4, 4) . "\n";

}

function send_demo($client)
{
    $time = date("Y-m-d H:i:s");
    $params = ['time' => $time];
    $data = ['service' => 'App/WebSocket/User/CheckService', 'operate' => 'tcp', 'params' => ['a' => 1, 'b' => 2]];
    $body = encode($data, 1);
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
    $client->send($resData);
    echo "package111" . "\n";

}

function websocket_demo()
{
    $data = ['service' => 'App/WebSocket/User/CheckService', 'operate' => 'tcp', 'params' => ['a' => 1, 'b' => 2]];
    $data = json_encode($data);
}

function encode($data, $serialize_type = 1)
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
