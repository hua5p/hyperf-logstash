<?php

/**
 * Hyperf Logstash 基本使用示例
 */

declare(strict_types=1);

use Hyperf\Logger\LoggerFactory;

// 示例 1: 基本日志记录
class ExampleService
{
    public function __construct(
        private LoggerFactory $loggerFactory
    ) {}

    public function processData()
    {
        $logger = $this->loggerFactory->get('default');

        $logger->info('开始处理数据', [
            'service' => 'ExampleService',
            'method' => 'processData',
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        try {
            // 模拟业务逻辑
            $result = $this->doSomething();

            $logger->info('数据处理完成', [
                'result' => $result,
                'processing_time' => 0.5
            ]);
        } catch (\Exception $e) {
            $logger->error('数据处理失败', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    private function doSomething()
    {
        return 'success';
    }
}

// 示例 2: 不同日志通道
class MultiChannelService
{
    public function __construct(
        private LoggerFactory $loggerFactory
    ) {}

    public function logToDifferentChannels()
    {
        // 默认通道
        $defaultLogger = $this->loggerFactory->get('default');
        $defaultLogger->info('默认通道日志');

        // SQL 通道
        $sqlLogger = $this->loggerFactory->get('sql');
        $sqlLogger->info('SQL 查询日志', [
            'query' => 'SELECT * FROM users',
            'execution_time' => 0.1
        ]);

        // 自定义通道
        $customLogger = $this->loggerFactory->get('custom');
        $customLogger->warning('自定义通道警告', [
            'module' => 'payment',
            'action' => 'process_payment'
        ]);
    }
}

// 示例 3: 结构化日志
class StructuredLoggingService
{
    public function __construct(
        private LoggerFactory $loggerFactory
    ) {}

    public function logUserAction(int $userId, string $action, array $context = [])
    {
        $logger = $this->loggerFactory->get('user-actions');

        $logData = [
            'user_id' => $userId,
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'context' => $context
        ];

        $logger->info("用户操作: {$action}", $logData);
    }

    public function logError(\Throwable $exception, array $context = [])
    {
        $logger = $this->loggerFactory->get('errors');

        $logData = [
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'context' => $context
        ];

        $logger->error('系统异常', $logData);
    }
}

// 示例 4: 性能监控日志
class PerformanceMonitoringService
{
    public function __construct(
        private LoggerFactory $loggerFactory
    ) {}

    public function logPerformance(string $operation, float $executionTime, array $metrics = [])
    {
        $logger = $this->loggerFactory->get('performance');

        $logData = [
            'operation' => $operation,
            'execution_time' => $executionTime,
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'metrics' => $metrics,
            'timestamp' => microtime(true)
        ];

        if ($executionTime > 1.0) {
            $logger->warning('慢操作检测', $logData);
        } else {
            $logger->info('操作完成', $logData);
        }
    }
}

echo "✅ Hyperf Logstash 使用示例已创建\n";
echo "📝 这些示例展示了如何在不同场景下使用日志功能\n";
echo "🔗 所有日志都会自动发送到 Logstash\n";
