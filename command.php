<?php
include_once './App/Config/defines.php';
$config = include_once './App/Config/config.php';
$serverConfig = include_once './App/Config/server.php';
$config = array_merge($config, $serverConfig);

include_once VENDOR_PATH . '/autoload.php';
date_default_timezone_set('PRC');

use Framework\SwServer\ServerManager;
use Symfony\Component\Console\Application;
use App\Cmd\ProcessCommand;

$application = new Application();

ServerManager::$config=$config;


// 注册我们编写的命令 (commands)
$application->add(new ProcessCommand());

$application->run();





