<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Hua5p\HyperfLogstash\Service\LogFactoryService;
use Hyperf\Coroutine\Coroutine;
use Hyperf\Coroutine\Parallel;

// 模拟 Hyperf 环境
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__ . '/../');
}

echo "开始协程并发测试...\n";

// 测试1：并发创建相同模块的日志
echo "\n=== 测试1：并发创建相同模块的日志 ===\n";

$parallel = new Parallel();
$results = [];

for ($i = 0; $i < 10; $i++) {
    $results[] = $parallel->add(function () use ($i) {
        $logger = LogFactoryService::createLogger('test', 'app');
        return [
            'coroutine_id' => Coroutine::id(),
            'logger_hash' => spl_object_hash($logger),
            'index' => $i
        ];
    });
}

$results = $parallel->wait();

// 检查所有logger是否相同（应该是单例）
$firstHash = $results[0]['logger_hash'];
$allSame = true;

foreach ($results as $result) {
    if ($result['logger_hash'] !== $firstHash) {
        $allSame = false;
        break;
    }
}

echo "所有协程创建的logger是否相同: " . ($allSame ? '✅ 是' : '❌ 否') . "\n";
echo "第一个logger哈希: {$firstHash}\n";

// 测试2：并发创建不同模块的日志
echo "\n=== 测试2：并发创建不同模块的日志 ===\n";

$parallel2 = new Parallel();
$results2 = [];

for ($i = 0; $i < 5; $i++) {
    $results2[] = $parallel2->add(function () use ($i) {
        $logger = LogFactoryService::createLogger("module{$i}", 'app');
        return [
            'coroutine_id' => Coroutine::id(),
            'logger_hash' => spl_object_hash($logger),
            'module' => "module{$i}"
        ];
    });
}

$results2 = $parallel2->wait();

// 检查不同模块的logger是否不同
$hashes = [];
foreach ($results2 as $result) {
    $hashes[$result['module']] = $result['logger_hash'];
}

$uniqueHashes = array_unique($hashes);
echo "不同模块的logger是否不同: " . (count($uniqueHashes) === count($hashes) ? '✅ 是' : '❌ 否') . "\n";
echo "创建的模块数量: " . count($hashes) . "\n";
echo "唯一logger数量: " . count($uniqueHashes) . "\n";

// 测试3：测试默认日志
echo "\n=== 测试3：并发创建默认日志 ===\n";

$parallel3 = new Parallel();
$results3 = [];

for ($i = 0; $i < 5; $i++) {
    $results3[] = $parallel3->add(function () use ($i) {
        $logger = LogFactoryService::createDefaultLogger();
        return [
            'coroutine_id' => Coroutine::id(),
            'logger_hash' => spl_object_hash($logger),
            'index' => $i
        ];
    });
}

$results3 = $parallel3->wait();

$firstDefaultHash = $results3[0]['logger_hash'];
$allDefaultSame = true;

foreach ($results3 as $result) {
    if ($result['logger_hash'] !== $firstDefaultHash) {
        $allDefaultSame = false;
        break;
    }
}

echo "所有协程创建的默认logger是否相同: " . ($allDefaultSame ? '✅ 是' : '❌ 否') . "\n";
echo "默认logger哈希: {$firstDefaultHash}\n";

// 测试4：实际写入日志
echo "\n=== 测试4：实际写入日志 ===\n";

$parallel4 = new Parallel();
$results4 = [];

for ($i = 0; $i < 3; $i++) {
    $results4[] = $parallel4->add(function () use ($i) {
        $logger = LogFactoryService::createLogger('coroutine-test', 'app');
        $logger->info("协程 {$i} 测试消息", ['coroutine_id' => Coroutine::id()]);
        return [
            'coroutine_id' => Coroutine::id(),
            'message' => "协程 {$i} 写入成功"
        ];
    });
}

$results4 = $parallel4->wait();

foreach ($results4 as $result) {
    echo "✅ {$result['message']} (协程ID: {$result['coroutine_id']})\n";
}

echo "\n=== 测试完成 ===\n";
echo "请检查 runtime/logs/coroutine-test/app.log 文件是否包含所有测试日志\n";
