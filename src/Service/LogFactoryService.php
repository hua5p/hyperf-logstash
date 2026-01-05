<?php

namespace Hua5p\HyperfLogstash\Service;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Hua5p\HyperfLogstash\Logger\LogstashQueueHandler;
use Hua5p\HyperfLogstash\Logger\UuidRequestIdProcessor;
use function Hyperf\Collection\data_get;
use function Hyperf\Support\env;
use Hyperf\Coroutine\Coroutine;

class LogFactoryService
{
    private static array $loggers = [];
    private static array $creating = [];

    public static function createLogger(string $module, string $type): Logger
    {
        $key = "{$module}-{$type}";

        // 如果已经创建过，直接返回
        if (isset(self::$loggers[$key])) {
            return self::$loggers[$key];
        }

        // 如果正在创建中，等待创建完成
        if (isset(self::$creating[$key])) {
            // 等待其他协程创建完成
            $maxWait = 10; // 最多等待10次
            $waitCount = 0;

            while (isset(self::$creating[$key]) && $waitCount < $maxWait) {
                Coroutine::sleep(0.001);
                $waitCount++;

                // 再次检查是否已经创建完成
                if (isset(self::$loggers[$key])) {
                    return self::$loggers[$key];
                }
            }
        }

        // 标记正在创建
        self::$creating[$key] = true;

        try {
            // 再次检查是否已被其他协程创建
            if (isset(self::$loggers[$key])) {
                return self::$loggers[$key];
            }

            // 创建新的logger
            self::$loggers[$key] = self::buildLogger($module, $type);
            return self::$loggers[$key];
        } finally {
            // 移除创建标记
            unset(self::$creating[$key]);
        }
    }

    /**
     * 创建默认日志实例（支持 Logstash）
     */
    public static function createDefaultLogger(): Logger
    {
        $key = 'default';

        // 如果已经创建过，直接返回
        if (isset(self::$loggers[$key])) {
            return self::$loggers[$key];
        }

        // 如果正在创建中，等待创建完成
        if (isset(self::$creating[$key])) {
            // 等待其他协程创建完成
            $maxWait = 10; // 最多等待10次
            $waitCount = 0;

            while (isset(self::$creating[$key]) && $waitCount < $maxWait) {
                Coroutine::sleep(0.001);
                $waitCount++;

                // 再次检查是否已经创建完成
                if (isset(self::$loggers[$key])) {
                    return self::$loggers[$key];
                }
            }
        }

        // 标记正在创建
        self::$creating[$key] = true;

        try {
            // 再次检查是否已被其他协程创建
            if (isset(self::$loggers[$key])) {
                return self::$loggers[$key];
            }

            // 创建新的logger
            self::$loggers[$key] = self::buildDefaultLogger();
            return self::$loggers[$key];
        } finally {
            // 移除创建标记
            unset(self::$creating[$key]);
        }
    }

    private static function buildLogger(string $module, string $type): Logger
    {
        $logger = new Logger("{$module}.{$type}");

        // 获取统一配置
        $config = self::getConfig();

        // 添加文件日志处理器（如果未禁用本地文件日志）
        if (!($config['disable_local_logs'] ?? false)) {
            $logPath = (defined('BASE_PATH') ? constant('BASE_PATH') : getcwd()) . "/runtime/logs/{$module}/{$type}.log";

            // 安全地创建目录
            self::ensureDirectoryExists(dirname($logPath));

            $fileHandler = new RotatingFileHandler(
                $logPath,
                (int)data_get($config, 'max_files', data_get($config['logstash'], 'max_files', 3)),
                Logger::INFO
            );

            $fileHandler->setFormatter(new LineFormatter(
                "[%datetime%] [request_id: %extra.request_id%] %message% %context%\n",
                data_get($config, 'date_format', data_get($config['logstash'], 'date_format', 'Y-m-d H:i:s')),
                true,
                true
            ));

            $logger->pushHandler($fileHandler);
        }

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
     * 安全地创建目录，处理并发情况
     */
    private static function ensureDirectoryExists(string $dir): void
    {
        if (is_dir($dir)) {
            return;
        }

        // 使用重试机制来处理并发创建目录的情况
        $maxRetries = 3;
        $retryCount = 0;

        while ($retryCount < $maxRetries) {
            try {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                return;
            } catch (\Exception $e) {
                $retryCount++;

                // 如果目录已经存在，说明其他协程已经创建成功
                if (is_dir($dir)) {
                    return;
                }

                // 最后一次重试失败，抛出异常
                if ($retryCount >= $maxRetries) {
                    throw $e;
                }

                // 短暂等待后重试
                \Hyperf\Coroutine\Coroutine::sleep(0.001 * $retryCount);
            }
        }
    }

    /**
     * 构建默认日志实例
     */
    private static function buildDefaultLogger(): Logger
    {
        $logger = new Logger('default');

        // 获取统一配置
        $config = self::getConfig();

        // 添加文件日志处理器（如果未禁用本地文件日志）
        if (!($config['disable_local_logs'] ?? false)) {
            $logPath = (defined('BASE_PATH') ? constant('BASE_PATH') : getcwd()) . "/runtime/logs/hyperf.log";

            // 安全地创建目录
            self::ensureDirectoryExists(dirname($logPath));

            $fileHandler = new RotatingFileHandler(
                $logPath,
                (int)$config['max_files'],
                Logger::INFO
            );

            $fileHandler->setFormatter(new LineFormatter(
                "[%datetime%] [request_id: %extra.request_id%] %message% %context%\n",
                $config['date_format'],
                true,
                true
            ));

            $logger->pushHandler($fileHandler);
        }

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
            'max_files' => env('LOGSTASH_MAX_FILES', 2),
            'date_format' => 'Y-m-d H:i:s',
            // 是否禁用本地文件日志，只写 Logstash
            'disable_local_logs' => env('LOGSTASH_DISABLE_LOCAL_LOGS', false),
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
