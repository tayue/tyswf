<?php

namespace App\Command;

use App\Listener\ConfirmMessageConsumeDelayListener;
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

class MessageConfirmDelayCommand extends Command
{
    protected function configure()
    {
        $this
            // 命令的名称 （"php console_command" 后面的部分）
            ->setName('handle2:create')
            // 运行 "php console_command list" 时的简短描述
            ->setDescription('Create new Process')
            // 运行命令时使用 "--help" 选项时的完整命令描述
            ->setHelp('This command allow you to create Process...')
            // 配置一个参数
            ->addArgument('process_name', InputArgument::REQUIRED, 'what\'s process name you want to create ?')
            ->addArgument('num', InputArgument::REQUIRED, 'what\'s numbers you want to create ?');


    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        include_once realpath('./') . '/Config/defines.php';
        $config = include_once realpath('./') . '/Config/config.php';
        $serverConfig = include_once realpath('./') . '/Config/server.php';
        $config = array_merge($config, $serverConfig);
        new CustomerProcess($input->getArgument('process_name'), "ConfirmMessageConsumeDelayListenerChild", $input->getArgument('num'), function($processIndex,$config){
            CommonService::setConfig($config);
            MessageService::ConfirmMessageConsumeDelayListener($processIndex);
        },$config);

        // 你想要做的任何操作
        //$optional_argument = $input->getArgument('optional_argument');

        $output->writeln('creating ConfirmMessageConsumeDelayListener...');
        $output->writeln('created ConfirmMessageConsumeDelayListener Procee :' . $input->getArgument('process_name') . ', num:' . $input->getArgument('num') . ' success !');


        $output->writeln('the end.');
    }
}