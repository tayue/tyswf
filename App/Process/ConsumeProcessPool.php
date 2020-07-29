<?php


namespace App\Process;

use Framework\SwServer\Coroutine\CoroutineManager;
use Swoole\Process\Pool;
use App\Service\CommonService;

class ConsumeProcessPool
{
    public $mpid = 0;
    public $works = [];
    public $max_process = 3;
    public $new_index = 0;
    private $processName;
    private $childProcessNamePre;
    private $callback;
    private $config;



    public function __construct($processName, $childProcessNamePre, $processNums = 0, $callback = null,$config=[])
    {
        try {
            $this->config = $config;
            $processNums && $this->max_process = $processNums;
            $this->processName = $processName;
            $this->childProcessNamePre = $childProcessNamePre;
            $this->callback=$callback;
            swoole_set_process_name(sprintf('ConsumeProcessPool-PS:%s', $this->processName));
            $this->mpid = posix_getpid();
            $this->run($callback);
        } catch (\Exception $e) {
            echo('ALL ERROR: ' . $e->getMessage());
        }
    }

    public function checkMpid(&$worker)
    {
        if (!\swoole_process::kill($this->mpid, 0)) { //向主进程发送信号检测主进程是否存活
            $worker->exit();
            // 这句提示,实际是看不到的.需要写到日志中
            echo "Master process exited, I [{$worker['pid']}] also quit\n";
        }
    }

    public function run($callback)
    {
        $pool = new Pool($this->max_process);
        $pool->on("WorkerStart", function ($pool, $workerId) use ($callback) {
            echo $this->childProcessNamePre . $workerId."\r\n";
            echo CoroutineManager::getInstance()->getCoroutineId()."@@@\r\n";
            swoole_set_process_name(sprintf('ConsumeProcessPool-PS:%s', $this->childProcessNamePre . $workerId));
            $this->checkMpid($this->mpid);
            CommonService::setConfig($this->config);
            $callback($this->childProcessNamePre . $workerId);
        });
        $pool->on("WorkerStop", function ($pool, $workerId) {
            echo "Worker#{$workerId} is stopped\n";
        });
        $pool->start();
    }

}

;
