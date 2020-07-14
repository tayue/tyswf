<?php
require './vendor/autoload.php';

ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);


use GuzzleHttp\Client;
use Framework\SwServer\Guzzle\CoroutineHandler;
use GuzzleHttp\HandlerStack;
use Swoole\Coroutine;

Coroutine::create(function (){
    $client = new Client([
        'base_uri' => 'http://192.168.99.88:9501/',
        'handler' => HandlerStack::create(new CoroutineHandler()),
        'timeout' => 5,
        'swoole' => [
            'timeout' => 10,
            'socket_buffer_size' => 1024 * 1024 * 2,
        ],
    ]);

    $response = $client->get('/');

    echo $response->getBody()->__toString()."\r\n";
});
