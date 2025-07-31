# Hyperf Logstash 简化配置指南

## 概述

经过优化后，Hyperf Logstash 包的配置已经大大简化。用户现在只需要一个配置文件即可完成所有配置。

## 配置简化对比

### 之前的配置方式（复杂）

用户需要配置多个文件：

1. `config/autoload/logger.php` - 日志配置
2. `config/autoload/processes.php` - 进程配置  
3. `config/autoload/aspects.php` - 切面配置
4. 手动注册依赖注入
5. 手动配置注解扫描

### 现在的配置方式（简化）

用户只需要：

1. 发布一个配置文件
2. 可选配置环境变量

## 安装步骤

### 1. 安装包

```bash
composer require hua5p/hyperf-logstash
```

### 2. 发布配置文件

```bash
php bin/hyperf.php vendor:publish hua5p/hyperf-logstash
```

这个命令会自动创建 `config/autoload/logstash.php` 文件，包含所有必要的配置。

### 3. 配置环境变量（可选）

在 `.env` 文件中配置：

```env
# Logstash 配置
LOGSTASH_HOST=192.168.31.210
LOGSTASH_PORT=5000
LOGSTASH_PROJECT=hua5Rec
LOGSTASH_TEAM=hua5p
LOGSTASH_ENABLED=true
```

### 4. 开始使用

现在你可以直接使用日志功能，无需额外配置！

## 配置文件结构

`config/autoload/logstash.php` 文件包含：

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
                // 文件处理器
                // Logstash 处理器
            ],
        ],
    ],

    // 进程配置
    'processes' => [
        LogstashQueueConsumer::class,
    ],

    // 切面配置
    'aspects' => [
        LogChannelAspect::class,
        HLoggerAspect::class,
    ],

    // 依赖注入配置
    'dependencies' => [
        LogFactoryService::class => LogFactoryService::class,
    ],

    // 注解扫描配置
    'annotations' => [
        'scan' => [
            'paths' => [
                __DIR__ . '/../../src',
            ],
        ],
    ],
];
```

## 自动注册的组件

发布配置文件后，以下组件会自动注册：

### 1. 进程
- `LogstashQueueConsumer` - 自动启动，处理日志队列

### 2. 切面
- `LogChannelAspect` - 处理 `@LogChannel` 注解
- `HLoggerAspect` - 处理 `@HLogger` 注解

### 3. 依赖注入
- `LogFactoryService` - 日志工厂服务

### 4. 注解扫描
- 自动扫描 `src/` 目录下的注解

## 使用示例

### 基本使用

```php
use Hyperf\Logger\LoggerFactory;

class UserService
{
    public function __construct(
        private LoggerFactory $loggerFactory
    ) {}

    public function createUser(array $userData)
    {
        $logger = $this->loggerFactory->get('default');
        $logger->info('开始创建用户', $userData);
        
        // 业务逻辑...
        
        $logger->info('用户创建成功');
    }
}
```

### 使用注解（推荐）

```php
use Hua5p\HyperfLogstash\Annotation\LogChannel;
use Hua5p\HyperfLogstash\Service\BaseService;

#[LogChannel(module: 'user', type: 'service')]
class UserService extends BaseService
{
    public function createUser(array $userData)
    {
        $this->logger->info('开始创建用户', $userData);
        
        try {
            // 业务逻辑...
            $this->logger->info('用户创建成功');
        } catch (\Exception $e) {
            $this->errorLogger->error('用户创建失败', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
```

## 优势

1. **配置简化**：从多个配置文件简化为一个配置文件
2. **自动注册**：进程、切面、依赖注入自动注册
3. **开箱即用**：安装后即可使用，无需额外配置
4. **环境变量支持**：通过环境变量灵活配置
5. **向后兼容**：保持原有的所有功能

## 验证

运行以下命令验证配置是否正确：

```bash
# 运行测试
./vendor/bin/phpunit

# 检查语法
php -l config/autoload/logstash.php
```

## 故障排除

### 1. 配置文件不存在

确保运行了发布命令：

```bash
php bin/hyperf.php vendor:publish hua5p/hyperf-logstash
```

### 2. 进程未启动

检查 `config/autoload/logstash.php` 中的 `processes` 配置是否正确。

### 3. 日志未发送到 Logstash

检查环境变量配置和 Logstash 服务是否正常运行。

## 总结

通过这次优化，Hyperf Logstash 包的使用体验得到了显著改善：

- ✅ 配置步骤从 5 步减少到 2 步
- ✅ 配置文件从 3 个减少到 1 个  
- ✅ 自动注册所有必要组件
- ✅ 保持所有原有功能
- ✅ 提供清晰的使用文档

用户现在可以更快速地集成和使用这个日志包。 