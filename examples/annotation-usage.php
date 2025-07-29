<?php

/**
 * Hyperf Logstash 注解日志使用示例
 */

declare(strict_types=1);

use Hua5p\HyperfLogstash\Annotation\LogChannel;
use Hua5p\HyperfLogstash\Service\BaseService;
use Hua5p\HyperfLogstash\Service\LogFactoryService;

// 示例 1: 继承基础服务类
#[LogChannel(module: 'recommendation', type: 'rule')]
class RecommendationService extends BaseService
{
    public function generateRecommendations()
    {
        $this->logger->info('开始生成推荐');

        try {
            // 业务逻辑
            $result = $this->doSomething();

            $this->logger->info('推荐生成完成', [
                'result_count' => count($result),
                'processing_time' => 0.5
            ]);
        } catch (\Exception $e) {
            $this->errorLogger->error('推荐生成失败', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    }

    private function doSomething()
    {
        return ['item1', 'item2', 'item3'];
    }
}

// 示例 2: 直接使用注解
#[LogChannel(module: 'indicator', type: 'buyer')]
class BuyerIndicatorService
{
    public $logger;
    public $errorLogger;
    public $debugLogger;

    public function calculateIndicators()
    {
        $this->logger->info('开始计算买家指标');

        try {
            // 计算逻辑
            $indicators = $this->calculate();

            $this->logger->info('指标计算完成', [
                'indicators_count' => count($indicators)
            ]);

            $this->debugLogger->debug('详细计算过程', [
                'calculation_steps' => ['step1', 'step2', 'step3']
            ]);
        } catch (\Exception $e) {
            $this->errorLogger->error('指标计算失败', [
                'error' => $e->getMessage()
            ]);
        }
    }

    private function calculate()
    {
        return ['indicator1' => 0.8, 'indicator2' => 0.6];
    }
}

// 示例 3: 多模块日志
#[LogChannel(module: 'payment', type: 'processor')]
class PaymentProcessor
{
    public $logger;
    public $errorLogger;

    public function processPayment(array $paymentData)
    {
        $this->logger->info('开始处理支付', [
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency']
        ]);

        try {
            // 支付处理逻辑
            $result = $this->process($paymentData);

            $this->logger->info('支付处理成功', [
                'transaction_id' => $result['transaction_id'],
                'status' => 'success'
            ]);
        } catch (\Exception $e) {
            $this->errorLogger->error('支付处理失败', [
                'error' => $e->getMessage(),
                'payment_data' => $paymentData
            ]);
        }
    }

    private function process(array $data)
    {
        return ['transaction_id' => 'txn_' . uniqid()];
    }
}

// 示例 4: 手动创建日志实例
class ManualLogService
{
    public function doSomething()
    {
        // 手动创建日志实例
        $logger = LogFactoryService::createLogger('manual', 'service');
        $errorLogger = LogFactoryService::createLogger('manual', 'error');

        $logger->info('手动创建的日志实例');
        $errorLogger->error('错误日志');
    }
}

// 示例 5: 不同模块的日志
#[LogChannel(module: 'user', type: 'action')]
class UserActionService
{
    public $logger;
    public $errorLogger;

    public function logUserAction(int $userId, string $action, array $context = [])
    {
        $logData = [
            'user_id' => $userId,
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'context' => $context
        ];

        $this->logger->info("用户操作: {$action}", $logData);
    }

    public function logError(\Throwable $exception, array $context = [])
    {
        $logData = [
            'exception_class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'context' => $context
        ];

        $this->errorLogger->error('系统异常', $logData);
    }
}

echo "✅ Hyperf Logstash 注解日志使用示例已创建\n";
echo "📝 这些示例展示了如何使用注解来自动注入日志实例\n";
echo "🔗 所有日志都会自动发送到 Logstash\n";
echo "�� 日志文件会按模块分别存储\n";
