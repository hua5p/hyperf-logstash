<?php

namespace Hua5p\HyperfLogstash\Service;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Hua5p\HyperfLogstash\Logger\LogstashQueueHandler;
use Hua5p\HyperfLogstash\Logger\UuidRequestIdProcessor;
use function Hyperf\Collection\data_get;
use function Hyperf\Support\env;

class LogFactoryService
{
    private static array $loggers = [];

    public static function createLogger(string $module, string $type): Logger
    {
        $key = "{$module}-{$type}";

        if (!isset(self::$loggers[$key])) {
            self::$loggers[$key] = self::buildLogger($module, $type);
        }

        return self::$loggers[$key];
    }

    /**
     * 创建默认日志实例（支持 Logstash）
     */
    public static function createDefaultLogger(): Logger
    {
        if (!isset(self::$loggers['default'])) {
            self::$loggers['default'] = self::buildDefaultLogger();
        }

        return self::$loggers['default'];
    }

    private static function buildLogger(string $module, string $type): Logger
    {
        $logger = new Logger("{$module}.{$type}");

        // 获取统一配置
        $config = self::getConfig();

        // 添加文件日志处理器
        $logPath = (defined('BASE_PATH') ? constant('BASE_PATH') : getcwd()) . "/runtime/logs/{$module}/{$type}.log";

        // 确保目录存在
        $dir = dirname($logPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fileHandler = new RotatingFileHandler(
            $logPath,
            data_get($config, 'max_files', data_get($config['logstash'], 'max_files', 30)),
            Logger::INFO
        );

        $fileHandler->setFormatter(new LineFormatter(
            "[%datetime%] [request_id: %extra.request_id%] %message% %context%\n",
            data_get($config, 'date_format', data_get($config['logstash'], 'date_format', 'Y-m-d H:i:s')),
            true,
            true
        ));

        $logger->pushHandler($fileHandler);

        // 添加 Logstash 队列处理器（如果配置启用）
        if ($config['logstash']['enabled'] ?? false) {
            $logstashHandler = new LogstashQueueHandler(
                $config['logstash']['host'] ?? '192.168.31.210',
                $config['logstash']['port'] ?? 5000,
                $config['logstash']['project'] ?? 'hua5rec',
                $module,
                $config['logstash']['team'] ?? 'hua5p',
                Logger::INFO
            );
            $logger->pushHandler($logstashHandler);
        }

        // 添加 request_id 处理器
        $logger->pushProcessor(new UuidRequestIdProcessor());

        return $logger;
    }

    /**
     * 构建默认日志实例
     */
    private static function buildDefaultLogger(): Logger
    {
        $logger = new Logger('default');

        // 获取统一配置
        $config = self::getConfig();

        // 添加文件日志处理器
        $logPath = (defined('BASE_PATH') ? constant('BASE_PATH') : getcwd()) . "/runtime/logs/hyperf.log";

        $fileHandler = new RotatingFileHandler(
            $logPath,
            $config['max_files'],
            Logger::INFO
        );

        $fileHandler->setFormatter(new LineFormatter(
            "[%datetime%] [request_id: %extra.request_id%] %message% %context%\n",
            $config['date_format'],
            true,
            true
        ));

        $logger->pushHandler($fileHandler);

        // 添加 Logstash 队列处理器（如果配置启用）
        if ($config['logstash']['enabled'] ?? false) {
            $logstashHandler = new LogstashQueueHandler(
                $config['logstash']['host'] ?? '192.168.31.210',
                $config['logstash']['port'] ?? 5000,
                $config['logstash']['project'] ?? 'hua5rec',
                'default', // 使用 'default' 作为模块名
                $config['logstash']['team'] ?? 'hua5p',
                Logger::INFO
            );
            $logger->pushHandler($logstashHandler);
        }

        // 添加 request_id 处理器
        $logger->pushProcessor(new UuidRequestIdProcessor());

        return $logger;
    }

    /**
     * 获取统一日志配置
     */
    private static function getConfig(): array
    {
        // 默认配置
        $defaultConfig = [
            'max_files' => 7,
            'date_format' => 'Y-m-d H:i:s',
            'logstash' => [
                'enabled' => env('LOGSTASH_ENABLED', false),
                'host' => env('LOGSTASH_HOST', '192.168.31.210'),
                'port' => env('LOGSTASH_PORT', 5000),
                'project' => env('LOGSTASH_PROJECT', 'hua5rec'),
                'team' => env('LOGSTASH_TEAM', 'hua5p'),
            ],
        ];

        // 在 Hyperf 包中，我们直接使用默认配置
        // 用户可以通过环境变量来覆盖配置

        return $defaultConfig;
    }
}
