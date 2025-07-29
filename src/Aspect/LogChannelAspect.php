<?php

namespace Hua5p\HyperfLogstash\Aspect;

use Hua5p\HyperfLogstash\Annotation\LogChannel;
use Hua5p\HyperfLogstash\Service\LogFactoryService;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
class LogChannelAspect extends AbstractAspect
{
    public array $annotations = [LogChannel::class];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        try {
            $instance = $proceedingJoinPoint->getInstance();
            $reflection = new \ReflectionClass($instance);

            // 获取类注解
            $attributes = $reflection->getAttributes(LogChannel::class);
            if (!empty($attributes)) {
                $logChannel = $attributes[0]->newInstance();

                // 注入日志实例
                $this->injectLogger($instance, $logChannel);
            }

            return $proceedingJoinPoint->process();
        } catch (\Throwable $e) {
            // 记录切面处理异常
            $this->logError('LogChannelAspect process error: ' . $e->getMessage(), $e);
            throw $e;
        }
    }

    /**
     * 注入日志实例到目标对象
     */
    private function injectLogger(object $instance, LogChannel $logChannel): void
    {
        // 注入普通日志实例
        if (property_exists($instance, 'logger')) {
            $instance->logger = LogFactoryService::createLogger($logChannel->module, $logChannel->type);
        }

        // 注入错误日志实例
        if (property_exists($instance, 'errorLogger')) {
            $instance->errorLogger = LogFactoryService::createLogger($logChannel->module, 'error');
        }

        // 注入调试日志实例（可选）
        if (property_exists($instance, 'debugLogger')) {
            $instance->debugLogger = LogFactoryService::createLogger($logChannel->module, 'debug');
        }
    }

    /**
     * 记录错误日志
     */
    private function logError(string $message, \Throwable $exception): void
    {
        try {
            $logger = LogFactoryService::createLogger('aspect', 'error');
            $logger->error($message, [
                'exception' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);
        } catch (\Throwable $e) {
            // 如果日志记录失败，至少输出到标准错误
            error_log("LogChannelAspect error: {$message}");
        }
    }
}
