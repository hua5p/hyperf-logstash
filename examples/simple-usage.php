<?php

/**
 * Hyperf Logstash 简化使用示例
 * 展示多种日志使用方式
 */

declare(strict_types=1);

use Hua5p\HyperfLogstash\Logger\Log;
use Hua5p\HyperfLogstash\Trait\LoggerTrait;
use Hua5p\HyperfLogstash\Annotation\HLogger;

// 方式 1: 使用静态门面类（推荐）
class UserService
{
    public function createUser(array $userData)
    {
        // 记录业务日志
        Log::business('user', 'create_user', $userData, [
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);

        try {
            // 业务逻辑
            $userId = $this->doCreateUser($userData);

            // 记录成功日志
            Log::info('user', '用户创建成功', [
                'user_id' => $userId,
                'email' => $userData['email']
            ]);

            return $userId;
        } catch (\Exception $e) {
            // 记录异常日志
            Log::exception('user', $e, '用户创建失败', [
                'user_data' => $userData
            ]);
            throw $e;
        }
    }

    private function doCreateUser(array $data): int
    {
        // 模拟创建用户
        return rand(1000, 9999);
    }
}

// 方式 2: 使用 Trait（推荐用于服务类）
class OrderService
{
    use LoggerTrait;

    public function __construct()
    {
        // 设置模块名
        $this->setLogModule('order');
    }

    public function createOrder(array $orderData)
    {
        // 记录业务日志
        $this->logBusiness('create_order', $orderData);

        try {
            // 业务逻辑
            $orderId = $this->processOrder($orderData);

            // 记录成功日志
            $this->logInfo('订单创建成功', [
                'order_id' => $orderId,
                'amount' => $orderData['amount']
            ]);

            return $orderId;
        } catch (\Exception $e) {
            // 记录异常日志
            $this->logException($e, '订单创建失败', [
                'order_data' => $orderData
            ]);
            throw $e;
        }
    }

    private function processOrder(array $data): int
    {
        // 模拟处理订单
        return rand(10000, 99999);
    }
}

// 方式 3: 使用注解（推荐用于关键方法）
class PaymentService
{
    use LoggerTrait;

    public function __construct()
    {
        $this->setLogModule('payment');
    }

    #[HLogger(
        message: '处理支付请求',
        level: 'info',
        logParams: true,
        logResult: true,
        logPerformance: true
    )]
    public function processPayment(array $paymentData)
    {
        // 业务逻辑会自动被注解记录
        return $this->doProcessPayment($paymentData);
    }

    #[HLogger(
        message: '验证支付',
        level: 'debug',
        logParams: true,
        logException: true
    )]
    public function validatePayment(string $paymentId)
    {
        // 验证逻辑
        if (empty($paymentId)) {
            throw new \InvalidArgumentException('支付ID不能为空');
        }
        return true;
    }

    private function doProcessPayment(array $data): array
    {
        // 模拟支付处理
        return [
            'transaction_id' => 'txn_' . uniqid(),
            'status' => 'success',
            'amount' => $data['amount']
        ];
    }
}

// 方式 4: 直接使用不同模块的日志
class MultiModuleService
{
    public function processUserAction(int $userId, string $action)
    {
        // 用户模块日志
        Log::info('user', "用户操作: {$action}", [
            'user_id' => $userId,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        // 行为分析模块日志
        Log::business('analytics', 'user_action', [
            'user_id' => $userId,
            'action' => $action,
            'session_id' => session_id() ?? 'unknown'
        ]);

        // 性能监控
        $startTime = microtime(true);
        $this->doProcessAction($userId, $action);
        $duration = microtime(true) - $startTime;

        Log::performance('analytics', 'process_user_action', $duration, [
            'user_id' => $userId,
            'action' => $action
        ]);
    }

    private function doProcessAction(int $userId, string $action): void
    {
        // 模拟处理
        usleep(rand(10000, 100000)); // 10-100ms
    }
}

echo "✅ Hyperf Logstash 简化使用示例已创建\n";
echo "📝 支持多种日志使用方式：\n";
echo "   - 静态门面类：Log::info('module', 'message')\n";
echo "   - Trait 方式：use LoggerTrait; \$this->logInfo('message')\n";
echo "   - 注解方式：#[Log] 自动记录方法调用\n";
echo "🔗 所有日志都会自动发送到 Logstash\n";
echo "📊 支持模块化、性能监控、异常记录等功能\n";
