<?php

namespace Hua5p\HyperfLogstash\Logger;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class UuidRequestIdProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        $record->extra['request_id'] = $this->getRequestId();
        return $record;
    }

    private function getRequestId(): string
    {
        // 尝试从 Hyperf 上下文获取 request_id
        try {
            if (class_exists('\Hyperf\Context\Context')) {
                $requestId = \Hyperf\Context\Context::get('request_id');
                if (is_string($requestId)) {
                    return $requestId;
                }
            }
        } catch (\Throwable $e) {
            // 忽略错误，使用默认值
        }

        // 尝试从 $_SERVER 获取
        if (isset($_SERVER['HTTP_X_REQUEST_ID'])) {
            return $_SERVER['HTTP_X_REQUEST_ID'];
        }

        // 生成新的 request_id
        return $this->generateRequestId();
    }

    private function generateRequestId(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
