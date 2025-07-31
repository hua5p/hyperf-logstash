<?php

/**
 * 单例模式测试示例
 * 验证日志实例是否真正实现了单例模式
 */

declare(strict_types=1);

use Hua5p\HyperfLogstash\Logger\Log;
use Hua5p\HyperfLogstash\Trait\LoggerTrait;

// 测试静态门面类的单例模式
echo "=== 测试静态门面类单例模式 ===\n";

$logger1 = Log::channel('user', 'app');
$logger2 = Log::channel('user', 'app');
$logger3 = Log::channel('user', 'error');

echo "相同模块和类型：" . ($logger1 === $logger2 ? "✅ 单例模式正常" : "❌ 单例模式异常") . "\n";
echo "不同模块或类型：" . ($logger1 !== $logger3 ? "✅ 不同实例正常" : "❌ 实例区分异常") . "\n";

// 测试 Trait 的单例模式
echo "\n=== 测试 Trait 单例模式 ===\n";

class TestService1
{
    use LoggerTrait;

    public function __construct()
    {
        $this->setLogModule('order');
    }

    public function testLogger()
    {
        $logger1 = $this->getLogger();
        $logger2 = $this->getLogger();
        $errorLogger = $this->getErrorLogger();

        echo "相同实例：" . ($logger1 === $logger2 ? "✅ 单例模式正常" : "❌ 单例模式异常") . "\n";
        echo "不同实例：" . ($logger1 !== $errorLogger ? "✅ 不同实例正常" : "❌ 实例区分异常") . "\n";
    }
}

class TestService2
{
    use LoggerTrait;

    public function __construct()
    {
        $this->setLogModule('payment');
    }

    public function testLogger()
    {
        $logger1 = $this->getLogger();
        $logger2 = $this->getLogger();

        echo "不同服务相同实例：" . ($logger1 === $logger2 ? "✅ 单例模式正常" : "❌ 单例模式异常") . "\n";
    }
}

$service1 = new TestService1();
$service1->testLogger();

$service2 = new TestService2();
$service2->testLogger();

// 测试性能
echo "\n=== 性能测试 ===\n";

$startTime = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    Log::channel('test', 'app');
}
$duration = microtime(true) - $startTime;
echo "1000次调用耗时：" . round($duration * 1000, 2) . "ms\n";

echo "\n✅ 单例模式测试完成！\n";
