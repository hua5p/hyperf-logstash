<?php

declare(strict_types=1);

namespace Hua5p\HyperfLogstash\Aspect;

use Hua5p\HyperfLogstash\Annotation\HLogger;
use Hua5p\HyperfLogstash\Logger\Log as Logger;
use Hyperf\Di\Annotation\Aspect;
use Hyperf\Di\Aop\AbstractAspect;
use Hyperf\Di\Aop\ProceedingJoinPoint;

#[Aspect]
class HLoggerAspect extends AbstractAspect
{
    public array $annotations = [HLogger::class];

    public function process(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $annotation = $proceedingJoinPoint->getAnnotationMetadata()->method[HLogger::class] ?? null;
        if (!$annotation) {
            return $proceedingJoinPoint->process();
        }

        $startTime = microtime(true);
        $module = $annotation->module ?? $this->getModuleFromClass($proceedingJoinPoint->getInstance());

        try {
            // 记录方法调用开始
            if ($annotation->message) {
                $context = $annotation->context;
                if ($annotation->logParams) {
                    $context['params'] = $proceedingJoinPoint->getArguments();
                }

                Logger::{$annotation->level}($module, $annotation->message, $context, $annotation->type);
            }

            // 执行原方法
            $result = $proceedingJoinPoint->process();

            // 记录方法调用结束
            if ($annotation->logResult) {
                Logger::info($module, "方法执行完成", [
                    'result' => $result,
                    'params' => $annotation->logParams ? $proceedingJoinPoint->getArguments() : null
                ], $annotation->type);
            }

            // 记录性能日志
            if ($annotation->logPerformance) {
                $duration = microtime(true) - $startTime;
                $methodName = $proceedingJoinPoint->getReflectMethod()->getName();
                Logger::performance($module, $methodName, $duration, [
                    'params' => $annotation->logParams ? $proceedingJoinPoint->getArguments() : null
                ]);
            }

            return $result;
        } catch (\Throwable $exception) {
            // 记录异常日志
            if ($annotation->logException) {
                Logger::exception($module, $exception, $annotation->message ?: '方法执行异常', [
                    'params' => $annotation->logParams ? $proceedingJoinPoint->getArguments() : null
                ], $annotation->type);
            }
            throw $exception;
        }
    }

    /**
     * 从类名推断模块名
     */
    private function getModuleFromClass(object $instance): string
    {
        $className = get_class($instance);
        $parts = explode('\\', $className);
        $lastPart = end($parts);

        // 移除 Service、Controller、Job 等后缀
        $module = preg_replace('/(Service|Controller|Job|Listener|Handler)$/', '', $lastPart);

        return strtolower($module);
    }
}
