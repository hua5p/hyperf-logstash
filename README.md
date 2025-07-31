# Hyperf Logstash

Hyperf Logstash 集成包，提供开箱即用的日志解决方案，支持模块化日志记录和 Logstash 集成。

## 特性

- 🚀 **开箱即用**：无需复杂配置，安装即可使用
- 📊 **模块化日志**：支持按模块分离日志文件
- 🔗 **Logstash 集成**：自动发送日志到 Logstash
- 🎯 **多种使用方式**：静态门面、Trait、注解等多种选择
- ⚡ **性能监控**：内置性能日志记录
- 🛡️ **异常处理**：自动记录异常信息
- 🔧 **灵活配置**：支持环境变量配置
- 🚀 **单例模式**：日志实例缓存，避免重复创建

## 安装

```bash
composer require hua5p/hyperf-logstash
```

## 快速配置

### 1. 发布配置文件

```bash
php bin/hyperf.php vendor:publish hua5p/hyperf-logstash
```

### 2. 配置环境变量（可选）

在 `.env` 文件中配置：

```env
# Logstash 配置
LOGSTASH_HOST=192.168.31.210
LOGSTASH_PORT=5000
LOGSTASH_PROJECT=hua5Rec
LOGSTASH_TEAM=hua5p
LOGSTASH_ENABLED=true
```

### 3. 开始使用

现在你可以直接使用日志功能，无需额外配置！

## 快速开始

### 方式 1: 静态门面类（推荐）

```php
use Hua5p\HyperfLogstash\Logger\Log;

// 记录信息日志
Log::info('user', '用户登录成功', ['user_id' => 123]);

// 记录业务日志
Log::business('order', 'create_order', ['amount' => 100]);

// 记录异常日志
try {
    // 业务逻辑
} catch (\Exception $e) {
    Log::exception('user', $e, '用户操作失败');
}
```

### 方式 2: 使用 Trait（推荐用于服务类）

```php
use Hua5p\HyperfLogstash\Trait\LoggerTrait;

class UserService
{
    use LoggerTrait;

    public function __construct()
    {
        $this->setLogModule('user');
    }

    public function createUser(array $userData)
    {
        $this->logBusiness('create_user', $userData);
        
        try {
            // 业务逻辑
            $this->logInfo('用户创建成功', ['user_id' => 123]);
        } catch (\Exception $e) {
            $this->logException($e, '用户创建失败');
        }
    }
}
```

### 方式 3: 使用注解（推荐用于关键方法）

```php
use Hua5p\HyperfLogstash\Annotation\HLogger;

class PaymentService
{
    #[HLogger(
        message: '处理支付请求',
        logParams: true,
        logResult: true,
        logPerformance: true
    )]
    public function processPayment(array $paymentData)
    {
        // 业务逻辑会自动被注解记录
        return $this->doProcessPayment($paymentData);
    }
}
```

## 配置

### 环境变量配置

在 `.env` 文件中配置：

```env
# Logstash 配置
LOGSTASH_HOST=192.168.31.210
LOGSTASH_PORT=5000
LOGSTASH_PROJECT=hua5Rec
LOGSTASH_TEAM=hua5p
LOGSTASH_ENABLED=true
```

### 自定义配置

如果需要自定义配置，可以修改 `config/autoload/logstash.php` 文件：

```php
<?php

return [
    // Logstash 连接配置
    'logstash' => [
        'host' => env('LOGSTASH_HOST', '192.168.31.210'),
        'port' => env('LOGSTASH_PORT', 5000),
        'project' => env('LOGSTASH_PROJECT', 'hua5Rec'),
        'team' => env('LOGSTASH_TEAM', 'hua5p'),
        'enabled' => env('LOGSTASH_ENABLED', true),
    ],
    
    // 日志配置
    'logger' => [
        'default' => [
            'handlers' => [
                // 你的自定义处理器...
            ],
        ],
    ],
    
    // 其他配置...
];
```

## 日志模块

支持按模块分离日志文件：

```
runtime/logs/
├── user/
│   ├── app.log
│   ├── error.log
│   └── business.log
├── order/
│   ├── app.log
│   ├── error.log
│   └── business.log
└── payment/
    ├── app.log
    ├── error.log
    └── business.log
```

## API 参考

### 静态门面类

```php
// 基础日志方法
Log::debug(string $module, string $message, array $context = [], string $type = 'app')
Log::info(string $module, string $message, array $context = [], string $type = 'app')
Log::warning(string $module, string $message, array $context = [], string $type = 'app')
Log::error(string $module, string $message, array $context = [], string $type = 'error')

// 特殊日志方法
Log::exception(string $module, \Throwable $exception, string $message = '', array $context = [])
Log::business(string $module, string $action, array $data = [], array $context = [])
Log::performance(string $module, string $operation, float $duration, array $context = [])

// 获取日志实例
Log::channel(string $module, string $type = 'app'): Logger
```

### Trait 方法

```php
// 设置模块
$this->setLogModule(string $module)
$this->setLogType(string $type)

// 获取日志实例
$this->getLogger(): Logger
$this->getErrorLogger(): Logger
$this->getDebugLogger(): Logger
$this->getBusinessLogger(): Logger
$this->getPerformanceLogger(): Logger

// 记录日志
$this->logInfo(string $message, array $context = [])
$this->logDebug(string $message, array $context = [])
$this->logWarning(string $message, array $context = [])
$this->logError(string $message, array $context = [])
$this->logException(\Throwable $exception, string $message = '', array $context = [])
$this->logBusiness(string $action, array $data = [], array $context = [])
$this->logPerformance(string $operation, float $duration, array $context = [])
```

### 注解参数

```php
#[HLogger(
    message: string,           // 日志消息
    level: string,            // 日志级别 (debug|info|warning|error)
    context: array,           // 上下文数据
    logParams: bool,          // 是否记录方法参数
    logResult: bool,          // 是否记录返回值
    logException: bool,       // 是否记录异常
    logPerformance: bool,     // 是否记录性能
    module: ?string,          // 模块名（可选，自动推断）
    type: string             // 日志类型
)]
```

## 示例

查看 `examples/` 目录下的完整示例：

- `simple-usage.php` - 简化使用示例
- `basic-usage.php` - 基础使用示例
- `annotation-usage.php` - 注解使用示例

## 许可证

MIT License 