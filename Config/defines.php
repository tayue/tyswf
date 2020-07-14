<?php
defined('APP_NAME') or define('APP_NAME','App');
defined('APP_PATH') or define('APP_PATH',dirname(__DIR__)."/App");
defined('ROOT_PATH') or define('ROOT_PATH',dirname(APP_PATH));
defined('DATA_PATH') or define('DATA_PATH',ROOT_PATH.'/Data');
defined('CONFIG_PATH') or define('CONFIG_PATH',ROOT_PATH.'/Config');
defined('VENDOR_PATH') or define('VENDOR_PATH',ROOT_PATH.'/vendor');

// 定义服务协议常量
defined('SWOOLE_HTTP') or define('SWOOLE_HTTP', 'http');
defined('SWOOLE_WEBSOCKET') or define('SWOOLE_WEBSOCKET', 'websocket');
defined('SWOOLE_TCP') or define('SWOOLE_TCP', 'tcp');
defined('SWOOLE_UDP') or define('SWOOLE_UDP', 'udp');

// 定义打包检查类型
defined('SWOOLE_PACK_CHECK_LENGTH') or define('SWOOLE_PACK_CHECK_LENGTH', 'length');
defined('SWOOLE_PACK_CHECK_EOF') or define('SWOOLE_PACK_CHECK_EOF', 'eof');

// 日志目录
defined('LOG_PATH') or define('LOG_PATH',APP_PATH.'/Log');

defined('TEXT_MAP') or define('TEXT_MAP','text_map');

// 定义smarty
defined('SMARTY_TEMPLATE_PATH') or define('SMARTY_TEMPLATE_PATH',APP_PATH.'/View/');
defined('SMARTY_COMPILE_DIR') or define('SMARTY_COMPILE_DIR',APP_PATH.'/Runtime/');
defined('SMARTY_CACHE_DIR') or define('SMARTY_CACHE_DIR',APP_PATH.'/Runtime/');