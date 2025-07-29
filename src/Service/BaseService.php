<?php

namespace Hua5p\HyperfLogstash\Service;

use Monolog\Logger;

/**
 * 基础服务类
 * 提供日志实例的自动注入功能
 */
abstract class BaseService
{
    /**
     * 普通日志实例
     */
    public Logger $logger;

    /**
     * 错误日志实例
     */
    public Logger $errorLogger;

    /**
     * 调试日志实例
     */
    public Logger $debugLogger;

    /**
     * 构造函数
     * 子类可以通过 LogChannel 注解来自动注入日志实例
     */
    public function __construct()
    {
        // 日志实例会通过 LogChannelAspect 自动注入
        // 子类只需要定义 public $logger, $errorLogger, $debugLogger 属性
    }

    /**
     * 手动创建日志实例（如果注解注入失败）
     */
    protected function createLogger(string $module, string $type): Logger
    {
        return LogFactoryService::createLogger($module, $type);
    }

    /**
     * 手动创建错误日志实例
     */
    protected function createErrorLogger(string $module): Logger
    {
        return LogFactoryService::createLogger($module, 'error');
    }

    /**
     * 手动创建调试日志实例
     */
    protected function createDebugLogger(string $module): Logger
    {
        return LogFactoryService::createLogger($module, 'debug');
    }
}
