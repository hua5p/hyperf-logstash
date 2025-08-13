<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hua5p\HyperfLogstash\Service\LogFactoryService;

// 模拟 Hyperf 环境
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/../');
}

echo "=== Logstash 专用模式测试 ===\n";

// 测试1：默认模式（同时写本地和 Logstash）
echo "\n1. 测试默认模式（同时写本地和 Logstash）\n";
echo "设置环境变量：LOGSTASH_ENABLED=true, LOGSTASH_DISABLE_LOCAL_LOGS=false\n";

// 设置环境变量
putenv('LOGSTASH_ENABLED=true');
putenv('LOGSTASH_DISABLE_LOCAL_LOGS=false');

$logger1 = LogFactoryService::createLogger('test', 'app');
$logger1->info('这是默认模式的测试消息', ['mode' => 'default']);

echo "✅ 默认模式测试完成\n";
echo "检查文件：runtime/logs/test/app.log 应该存在\n";

// 测试2：Logstash 专用模式（只写 Logstash）
echo "\n2. 测试 Logstash 专用模式（只写 Logstash）\n";
echo "设置环境变量：LOGSTASH_ENABLED=true, LOGSTASH_DISABLE_LOCAL_LOGS=true\n";

// 设置环境变量
putenv('LOGSTASH_ENABLED=true');
putenv('LOGSTASH_DISABLE_LOCAL_LOGS=true');

// 清除之前的缓存
$reflection = new ReflectionClass(LogFactoryService::class);
$loggersProperty = $reflection->getProperty('loggers');
$loggersProperty->setAccessible(true);
$loggersProperty->setValue(null, []);

$logger2 = LogFactoryService::createLogger('test', 'app');
$logger2->info('这是 Logstash 专用模式的测试消息', ['mode' => 'logstash-only']);

echo "✅ Logstash 专用模式测试完成\n";
echo "检查文件：runtime/logs/test/app.log 应该不存在或未更新\n";

// 测试3：验证处理器数量
echo "\n3. 验证日志处理器数量\n";

$handlers1 = $logger1->getHandlers();
$handlers2 = $logger2->getHandlers();

echo "默认模式处理器数量: " . count($handlers1) . "\n";
echo "Logstash 专用模式处理器数量: " . count($handlers2) . "\n";

// 分析处理器类型
$defaultHandlers = [];
foreach ($handlers1 as $handler) {
    $defaultHandlers[] = get_class($handler);
}

$logstashOnlyHandlers = [];
foreach ($handlers2 as $handler) {
    $logstashOnlyHandlers[] = get_class($handler);
}

echo "默认模式处理器类型: " . implode(', ', $defaultHandlers) . "\n";
echo "Logstash 专用模式处理器类型: " . implode(', ', $logstashOnlyHandlers) . "\n";

// 测试4：测试默认日志实例
echo "\n4. 测试默认日志实例\n";

// 清除缓存
$loggersProperty->setValue(null, []);

$defaultLogger = LogFactoryService::createDefaultLogger();
$defaultLogger->info('这是默认日志实例的测试消息', ['instance' => 'default']);

$defaultHandlers = $defaultLogger->getHandlers();
echo "默认日志实例处理器数量: " . count($defaultHandlers) . "\n";

$defaultHandlerTypes = [];
foreach ($defaultHandlers as $handler) {
    $defaultHandlerTypes[] = get_class($handler);
}
echo "默认日志实例处理器类型: " . implode(', ', $defaultHandlerTypes) . "\n";

// 测试5：测试不同模块
echo "\n5. 测试不同模块\n";

$logger3 = LogFactoryService::createLogger('user', 'login');
$logger3->info('用户登录测试消息', ['module' => 'user', 'action' => 'login']);

$logger4 = LogFactoryService::createLogger('order', 'payment');
$logger4->info('订单支付测试消息', ['module' => 'order', 'action' => 'payment']);

echo "✅ 不同模块测试完成\n";

// 测试6：验证配置读取
echo "\n6. 验证配置读取\n";

// 通过反射获取配置
$reflection = new ReflectionClass(LogFactoryService::class);
$getConfigMethod = $reflection->getMethod('getConfig');
$getConfigMethod->setAccessible(true);
$config = $getConfigMethod->invoke(null);

echo "当前配置：\n";
echo "- disable_local_logs: " . ($config['disable_local_logs'] ? 'true' : 'false') . "\n";
echo "- logstash.enabled: " . ($config['logstash']['enabled'] ? 'true' : 'false') . "\n";
echo "- logstash.host: " . $config['logstash']['host'] . "\n";
echo "- logstash.port: " . $config['logstash']['port'] . "\n";

echo "\n=== 测试完成 ===\n";
echo "\n使用说明：\n";
echo "1. 要启用 Logstash 专用模式，设置环境变量：\n";
echo "   LOGSTASH_ENABLED=true\n";
echo "   LOGSTASH_DISABLE_LOCAL_LOGS=true\n";
echo "\n2. 要恢复默认模式，设置环境变量：\n";
echo "   LOGSTASH_ENABLED=true\n";
echo "   LOGSTASH_DISABLE_LOCAL_LOGS=false\n";
echo "\n3. 检查 Redis 队列 'queue:logstash' 中是否有日志消息\n";
