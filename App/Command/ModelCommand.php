<?php

namespace App\Command;

use App\Listener\MessageConsumeOverTimeListener;
use App\Service\CommonService;
use App\Service\RabbitMqService;
use App\Service\MessageService;
use Framework\SwServer\Event\EventManager;
use Framework\SwServer\Pool\MysqlPoolManager;
use Framework\SwServer\Pool\RabbitPoolManager;
use Framework\SwServer\Pool\RedisPoolManager;
use Framework\SwServer\ServerManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Framework\SwServer\Process\CustomerProcess;

class ModelCommand extends Command
{
    protected function configure()
    {
        $this
            // 命令的名称 （"php console_command" 后面的部分）
            ->setName('process:create')
            // 运行 "php console_command list" 时的简短描述
            ->setDescription('Create new ConsumeProcess')
            // 运行命令时使用 "--help" 选项时的完整命令描述
            ->setHelp('This command allow you to create ConsumeProcess...')
            // 配置一个参数
            ->addArgument('num', InputArgument::REQUIRED, 'what\'s numbers you want to create ?')
            ->addArgument('process_name', InputArgument::REQUIRED, 'what\'s process name you want to create ?')
            ->addArgument('child_process_name', InputArgument::REQUIRED, 'what\'s child process name you want to create ?');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        include_once realpath('./') . '/Config/defines.php';
        $config = include_once realpath('./') . '/Config/config.php';
        $serverConfig = include_once realpath('./') . '/Config/server.php';
        $config = array_merge($config, $serverConfig);
        $output->writeln(json_encode($config['redis_pool']));
        // 你想要做的任何操作
        //$optional_argument = $input->getArgument('optional_argument');

        $output->writeln('creating ConsumeProcess...');
        //MessageService::MessageConsumeOverTimeHandle();
        $processName = 'MessageConsumeOverTimeListener';
        new CustomerProcess($input->getArgument('process_name'), $input->getArgument('child_process_name'), $input->getArgument('num'), [new MessageService(), 'ConsumeMessage'],$config);

//        new ConsumeProcess($input->getArgument('process_name'), $input->getArgument('child_process_name'), $input->getArgument('num'), function ($processName) {
//            $exchange = 'test_exchange_confirm';
//            $queue = 'test_queue_confirm';
//            $route_key = 'test_confirm';
//            $consumer_tag = 'consumer_tag';
//            $callback = [new RabbitMqService(), 'consumeCallback'];
//            RabbitMqService::consumeMessage($exchange, $queue, $route_key, $consumer_tag, $callback, true, $processName);
//        });
        $output->writeln('created Consume Procee :' . $input->getArgument('process_name') . ', num:' . $input->getArgument('num') . ' success !');


        $output->writeln('the end.');
    }
}