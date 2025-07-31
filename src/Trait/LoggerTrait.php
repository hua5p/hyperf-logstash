<?php

declare(strict_types=1);

namespace Hua5p\HyperfLogstash\Trait;

use Monolog\Logger;
use Hua5p\HyperfLogstash\Service\LogFactoryService;

/**
 * 日志 Trait
 * 为类提供模块化日志功能，支持自动注入和手动创建
 */
trait LoggerTrait
{
    /**
     * 模块名称
     */
    protected string $logModule = 'default';

    /**
     * 日志类型
     */
    protected string $logType = 'app';

    /**
     * 日志实例缓存
     */
    private static array $loggerCache = [];

    /**
     * 获取日志实例（单例）
     */
    protected function getLogger(): Logger
    {
        $key = "{$this->logModule}-{$this->logType}";
        if (!isset(self::$loggerCache[$key])) {
            self::$loggerCache[$key] = LogFactoryService::createLogger($this->logModule, $this->logType);
        }
        return self::$loggerCache[$key];
    }

    /**
     * 获取错误日志实例（单例）
     */
    protected function getErrorLogger(): Logger
    {
        $key = "{$this->logModule}-error";
        if (!isset(self::$loggerCache[$key])) {
            self::$loggerCache[$key] = LogFactoryService::createLogger($this->logModule, 'error');
        }
        return self::$loggerCache[$key];
    }

    /**
     * 获取调试日志实例（单例）
     */
    protected function getDebugLogger(): Logger
    {
        $key = "{$this->logModule}-debug";
        if (!isset(self::$loggerCache[$key])) {
            self::$loggerCache[$key] = LogFactoryService::createLogger($this->logModule, 'debug');
        }
        return self::$loggerCache[$key];
    }

    /**
     * 获取业务日志实例（单例）
     */
    protected function getBusinessLogger(): Logger
    {
        $key = "{$this->logModule}-business";
        if (!isset(self::$loggerCache[$key])) {
            self::$loggerCache[$key] = LogFactoryService::createLogger($this->logModule, 'business');
        }
        return self::$loggerCache[$key];
    }

    /**
     * 获取性能日志实例（单例）
     */
    protected function getPerformanceLogger(): Logger
    {
        $key = "{$this->logModule}-performance";
        if (!isset(self::$loggerCache[$key])) {
            self::$loggerCache[$key] = LogFactoryService::createLogger($this->logModule, 'performance');
        }
        return self::$loggerCache[$key];
    }

    /**
     * 记录信息日志
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $this->getLogger()->info($message, $context);
    }

    /**
     * 记录调试日志
     */
    protected function logDebug(string $message, array $context = []): void
    {
        $this->getDebugLogger()->debug($message, $context);
    }

    /**
     * 记录警告日志
     */
    protected function logWarning(string $message, array $context = []): void
    {
        $this->getLogger()->warning($message, $context);
    }

    /**
     * 记录错误日志
     */
    protected function logError(string $message, array $context = []): void
    {
        $this->getErrorLogger()->error($message, $context);
    }

    /**
     * 记录异常日志
     */
    protected function logException(\Throwable $exception, string $message = '', array $context = []): void
    {
        $context['exception'] = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ];

        $this->getErrorLogger()->error($message ?: $exception->getMessage(), $context);
    }

    /**
     * 记录业务日志
     */
    protected function logBusiness(string $action, array $data = [], array $context = []): void
    {
        $context['business_data'] = $data;
        $this->getBusinessLogger()->info("业务操作: {$action}", $context);
    }

    /**
     * 记录性能日志
     */
    protected function logPerformance(string $operation, float $duration, array $context = []): void
    {
        $context['duration'] = $duration;
        $context['duration_ms'] = round($duration * 1000, 2);
        $this->getPerformanceLogger()->info("性能监控: {$operation}", $context);
    }

    /**
     * 设置日志模块
     */
    protected function setLogModule(string $module): void
    {
        $this->logModule = $module;
    }

    /**
     * 设置日志类型
     */
    protected function setLogType(string $type): void
    {
        $this->logType = $type;
    }
}
