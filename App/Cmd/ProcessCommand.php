<?php

namespace App\Cmd;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Framework\SwServer\Pool\DiPool;

class ProcessCommand extends Command
{
    protected function configure()
    {
        DiPool::getInstance()->init();
        $this
            // 命令的名称 （"php console_command" 后面的部分）
            ->setName('process:create')
            // 运行 "php console_command list" 时的简短描述
            ->setDescription('Create new process')
            // 运行命令时使用 "--help" 选项时的完整命令描述
            ->setHelp('This command allow you to create process...')
            // 配置一个参数
            ->addArgument('name', InputArgument::REQUIRED, 'what\'s process you want to create ?')

            ->addArgument('val', InputArgument::REQUIRED, 'what\'s val you want to create ?')
            // 配置一个可选参数
            ->addArgument('optional_argument', InputArgument::OPTIONAL, 'this is a optional argument');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // 你想要做的任何操作
        $optional_argument = $input->getArgument('optional_argument');

        $output->writeln('creating...');
        $output->writeln('created name:' . $input->getArgument('name') . ', val:'.$input->getArgument('val').' success !');

        if ($optional_argument)
            $output->writeln('optional argument is ' . $optional_argument);

        $output->writeln('the end.');

       $userService=DiPool::getInstance()->get("userService");

        $workerNum = $input->getArgument('val');

        $pool = new \Swoole\Process\Pool($workerNum);

        $pool->on("WorkerStart", function ($pool, $workerId) use($userService) {
            echo "Worker#{$workerId} is started\n";
            $users=$userService->findUser(); //进程内执行逻辑业务代码
            print_r($users);
            $redis = new \Redis();
            $redis->pconnect('127.0.0.1', 6379);
            $key = "key1";
            while (true) {
                $msgs = $redis->brpop($key, 2);
                if ( $msgs == null) continue;
                var_dump($msgs);
            }
        });

        $pool->on("WorkerStop", function ($pool, $workerId) {
            echo "Worker#{$workerId} is stopped\n";
        });

        $pool->start();

        return 1;
    }
}