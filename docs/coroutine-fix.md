# 协程并发问题修复说明

## 问题描述

在协程环境中使用 `#[LogChannel]` 注解时，会出现以下错误：

```
LogChannelAspect error: LogChannelAspect process error: mkdir(): File exists
```

## 问题原因

1. **竞态条件**：多个协程同时调用 `LogFactoryService::createLogger()`
2. **目录创建冲突**：`mkdir()` 在多个协程同时执行时会出现 "File exists" 错误
3. **静态缓存问题**：`$loggers` 静态数组在协程环境下不是线程安全的

## 解决方案

### 1. 协程安全的日志缓存

使用 `$creating` 数组来标记正在创建的日志实例，防止重复创建：

```php
private static array $creating = [];

public static function createLogger(string $module, string $type): Logger
{
    $key = "{$module}-{$type}";

    // 如果已经创建过，直接返回
    if (isset(self::$loggers[$key])) {
        return self::$loggers[$key];
    }

    // 如果正在创建中，等待创建完成
    if (isset(self::$creating[$key])) {
        // 等待其他协程创建完成
        $maxWait = 10;
        $waitCount = 0;
        
        while (isset(self::$creating[$key]) && $waitCount < $maxWait) {
            Coroutine::sleep(0.001);
            $waitCount++;
            
            if (isset(self::$loggers[$key])) {
                return self::$loggers[$key];
            }
        }
    }

    // 标记正在创建
    self::$creating[$key] = true;

    try {
        // 再次检查是否已被其他协程创建
        if (isset(self::$loggers[$key])) {
            return self::$loggers[$key];
        }

        // 创建新的logger
        self::$loggers[$key] = self::buildLogger($module, $type);
        return self::$loggers[$key];
    } finally {
        // 移除创建标记
        unset(self::$creating[$key]);
    }
}
```

### 2. 安全的目录创建

使用重试机制来处理并发创建目录的情况：

```php
private static function ensureDirectoryExists(string $dir): void
{
    if (is_dir($dir)) {
        return;
    }

    // 使用重试机制来处理并发创建目录的情况
    $maxRetries = 3;
    $retryCount = 0;

    while ($retryCount < $maxRetries) {
        try {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            return;
        } catch (\Exception $e) {
            $retryCount++;
            
            // 如果目录已经存在，说明其他协程已经创建成功
            if (is_dir($dir)) {
                return;
            }
            
            // 最后一次重试失败，抛出异常
            if ($retryCount >= $maxRetries) {
                throw $e;
            }
            
            // 短暂等待后重试
            Coroutine::sleep(0.001 * $retryCount);
        }
    }
}
```

## 修复效果

### 修复前的问题
- ❌ 协程并发时出现 "mkdir(): File exists" 错误
- ❌ 日志实例可能重复创建
- ❌ 切面处理异常，影响业务逻辑

### 修复后的效果
- ✅ 协程安全的日志实例创建
- ✅ 防止目录创建冲突
- ✅ 单例模式正确工作
- ✅ 切面处理稳定可靠

## 测试验证

运行协程测试来验证修复效果：

```bash
php examples/coroutine-test.php
```

测试内容包括：
1. 并发创建相同模块的日志（验证单例）
2. 并发创建不同模块的日志（验证隔离）
3. 并发创建默认日志（验证默认实例）
4. 实际写入日志（验证功能完整性）

## 使用建议

1. **避免频繁创建**：尽量复用日志实例，避免在循环中频繁调用
2. **合理设置模块**：根据业务逻辑合理划分日志模块
3. **监控日志文件**：定期检查日志文件大小和数量
4. **性能优化**：在高并发场景下，考虑使用日志级别过滤

## 兼容性

- ✅ Hyperf 3.0+
- ✅ PHP 8.1+
- ✅ Swoole 5.0+
- ✅ 协程环境
- ✅ 传统同步环境

## 注意事项

1. 修复后的代码向后兼容，无需修改现有业务代码
2. 日志实例仍然使用单例模式，内存占用可控
3. 目录创建使用重试机制，提高了容错性
4. 协程等待时间很短（1ms），不会影响性能
