# Hyperf Logstash Integration Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hua5p/hyperf-logstash.svg)](https://packagist.org/packages/hua5p/hyperf-logstash)
[![Total Downloads](https://img.shields.io/packagist/dt/hua5p/hyperf-logstash.svg)](https://packagist.org/packages/hua5p/hyperf-logstash)
[![License](https://img.shields.io/packagist/l/hua5p/hyperf-logstash.svg)](https://packagist.org/packages/hua5p/hyperf-logstash)

Hyperf Logstash 集成包，为 Hyperf 框架提供集中式日志管理功能，支持异步队列处理和 ELK 栈集成。

## 特性

- ✅ **自动集成**：安装后自动为所有日志添加 Logstash 处理器
- ✅ **异步处理**：通过 Redis 队列异步发送日志，不影响主业务流程
- ✅ **多通道支持**：支持不同日志通道生成不同索引
- ✅ **注解日志**：支持 `#[LogChannel]` 注解自动注入日志实例
- ✅ **失败重试**：自动处理发送失败的消息
- ✅ **配置灵活**：支持环境变量和配置文件配置
- ✅ **零侵入**：无需修改现有代码

## 安装

```bash
composer require hua5p/hyperf-logstash
```

## 配置

### 1. 环境变量

在 `.env` 文件中添加以下配置：

```env
# 启用 Logstash 集成
LOGSTASH_ENABLED=true

# Logstash 服务地址
LOGSTASH_HOST=192.168.31.210
LOGSTASH_PORT=5000

# 项目配置
LOGSTASH_PROJECT=your-project
LOGSTASH_TEAM=your-team
```

### 2. 配置文件（可选）

如果需要自定义配置，可以在 `config/autoload/logger.php` 中覆盖默认配置：

```php
<?php

declare(strict_types=1);

use Hua5p\HyperfLogstash\Logger\LogstashQueueHandler;
use Monolog\Level;

return [
    'default' => [
        'handlers' => [
            // 文件处理器
            [
                'class' => \Monolog\Handler\RotatingFileHandler::class,
                'constructor' => [
                    'filename' => BASE_PATH . '/runtime/logs/hyperf.log',
                    'level' => Level::Debug,
                ],
            ],
            // Logstash 处理器
            [
                'class' => LogstashQueueHandler::class,
                'constructor' => [
                    'host' => env('LOGSTASH_HOST', '192.168.31.210'),
                    'port' => env('LOGSTASH_PORT', 5000),
                    'project' => env('LOGSTASH_PROJECT', 'your-project'),
                    'module' => 'default',
                    'team' => env('LOGSTASH_TEAM', 'your-team'),
                    'level' => Level::Info,
                ],
            ],
        ],
    ],
];
```

## 使用方法

### 1. 注解日志（推荐）

使用 `#[LogChannel]` 注解自动注入日志实例：

```php
<?php

namespace App\Service;

use Hua5p\HyperfLogstash\Annotation\LogChannel;
use Hua5p\HyperfLogstash\Service\BaseService;

#[LogChannel(module: 'recommendation', type: 'rule')]
class RecommendationService extends BaseService
{
    public function generateRecommendations()
    {
        $this->logger->info('开始生成推荐');
        
        try {
            // 业务逻辑
            $this->logger->info('推荐生成完成');
        } catch (\Exception $e) {
            $this->errorLogger->error('推荐生成失败', ['error' => $e->getMessage()]);
        }
    }
}
```

### 2. 直接使用注解

```php
<?php

namespace App\Service;

use Hua5p\HyperfLogstash\Annotation\LogChannel;

#[LogChannel(module: 'indicator', type: 'buyer')]
class BuyerIndicatorService
{
    public $logger;
    public $errorLogger;

    public function calculateIndicators()
    {
        $this->logger->info('开始计算指标');
        $this->errorLogger->error('计算失败', ['error' => '数据异常']);
    }
}
```

### 3. 自动生效

安装后，所有日志都会自动写到 Logstash，无需修改任何代码：

```php
<?php

namespace App\Service;

use Hyperf\Logger\LoggerFactory;

class ExampleService
{
    public function __construct(
        private LoggerFactory $loggerFactory
    ) {}

    public function doSomething()
    {
        // 这些日志会自动写到 Logstash
        $logger = $this->loggerFactory->get('default');
        $logger->info('开始执行任务', ['task_id' => 123]);
        $logger->error('任务执行失败', ['error' => '连接超时']);
    }
}
```

### 2. 框架日志

框架内部的日志也会自动写到 Logstash：
- SQL 查询日志
- 路由访问日志
- 中间件日志
- 异常日志
- 定时任务日志

## 日志索引格式

不同日志通道会生成不同的索引：

| 日志通道 | 索引格式 | 示例 |
|----------|----------|------|
| default | `{team}-{project}-default` | `your-team-your-project-default` |
| sql | `{team}-{project}-sql` | `your-team-your-project-sql` |
| other | `{team}-{project}-other` | `your-team-your-project-other` |

## Logstash 配置示例

```conf
# /etc/logstash/conf.d/hyperf-tcp.conf

input {
  tcp {
    port => 5000
    codec => json
  }
}

filter {
  # 处理时间戳
  if [datetime] {
    date {
      match => [ "datetime", "yyyy-MM-dd'T'HH:mm:ss.SSSSSSZZ" ]
      target => "@timestamp"
    }
  }

  # 处理索引信息
  if [index] {
    mutate {
      add_field => { "[@metadata][index]" => "%{[index]}" }
    }
  } else {
    mutate {
      add_field => { "[@metadata][index]" => "your-team-your-project-default" }
    }
  }
}

output {
  elasticsearch {
    hosts => ["localhost:9200"]
    index => "%{[@metadata][index]}"
    
    # 如果 Elasticsearch 需要认证
    # user => "elastic"
    # password => "your_password"
  }
}
```

## 监控和管理

### 1. 检查队列状态

```bash
# 检查 Redis 队列中的消息数量
redis-cli llen queue:logstash

# 检查失败队列
redis-cli llen queue:logstash:failed
```

### 2. 检查进程状态

```bash
# 检查 Logstash 消费进程
ps aux | grep logstash-queue-consumer
```

### 3. 查看日志

```bash
# 查看进程日志
tail -f runtime/logs/hyperf.log | grep logstash
```

## 故障排除

### 1. 日志没有出现在 Logstash

- 检查 `LOGSTASH_ENABLED` 环境变量
- 确认 Logstash 服务正在运行
- 检查网络连接和防火墙设置

### 2. 队列消息堆积

- 确认 `LogstashQueueConsumer` 进程正在运行
- 检查 Redis 连接
- 查看进程日志

### 3. 连接超时

- 检查 Logstash 服务状态
- 验证 IP 地址和端口配置
- 检查网络连通性

## 开发

### 1. 克隆仓库

```bash
git clone https://github.com/hua5p/hyperf-logstash.git
cd hyperf-logstash
composer install
```

### 2. 运行测试

```bash
composer test
```

### 3. 贡献代码

1. Fork 项目
2. 创建功能分支
3. 提交更改
4. 创建 Pull Request

## 许可证

MIT License - 详见 [LICENSE](LICENSE) 文件

## 支持

如有问题或建议，请提交 [Issue](https://github.com/hua5p/hyperf-logstash/issues) 