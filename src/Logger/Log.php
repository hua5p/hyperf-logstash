<?php

declare(strict_types=1);

namespace Hua5p\HyperfLogstash\Logger;

use Monolog\Logger;
use Hua5p\HyperfLogstash\Service\LogFactoryService;

/**
 * 静态日志门面类
 * 支持模块化日志，保持原有的模块分离优势
 */
class Log
{
    /**
     * 日志实例缓存
     */
    private static array $loggerCache = [];

    /**
     * 获取指定模块和类型的日志实例（单例）
     */
    public static function channel(string $module, string $type = 'app'): Logger
    {
        $key = "{$module}-{$type}";
        if (!isset(self::$loggerCache[$key])) {
            self::$loggerCache[$key] = LogFactoryService::createLogger($module, $type);
        }
        return self::$loggerCache[$key];
    }

    /**
     * 获取默认日志实例
     */
    public static function getLogger(): Logger
    {
        return LogFactoryService::createDefaultLogger();
    }

    /**
     * 记录调试日志到指定模块
     */
    public static function debug(string $module, string $message, array $context = [], string $type = 'app'): void
    {
        self::channel($module, $type)->debug($message, $context);
    }

    /**
     * 记录信息日志到指定模块
     */
    public static function info(string $module, string $message, array $context = [], string $type = 'app'): void
    {
        self::channel($module, $type)->info($message, $context);
    }

    /**
     * 记录警告日志到指定模块
     */
    public static function warning(string $module, string $message, array $context = [], string $type = 'app'): void
    {
        self::channel($module, $type)->warning($message, $context);
    }

    /**
     * 记录错误日志到指定模块
     */
    public static function error(string $module, string $message, array $context = [], string $type = 'error'): void
    {
        self::channel($module, $type)->error($message, $context);
    }

    /**
     * 记录异常日志到指定模块
     */
    public static function exception(string $module, \Throwable $exception, string $message = '', array $context = [], string $type = 'error'): void
    {
        $context['exception'] = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];

        self::channel($module, $type)->error($message ?: $exception->getMessage(), $context);
    }

    /**
     * 记录业务日志
     */
    public static function business(string $module, string $action, array $data = [], array $context = []): void
    {
        $context['business_data'] = $data;
        self::channel($module, 'business')->info("业务操作: {$action}", $context);
    }

    /**
     * 记录性能日志
     */
    public static function performance(string $module, string $operation, float $duration, array $context = []): void
    {
        $context['duration'] = $duration;
        $context['duration_ms'] = round($duration * 1000, 2);
        self::channel($module, 'performance')->info("性能监控: {$operation}", $context);
    }
}
