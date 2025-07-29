# 安装指南

## 快速开始

### 1. 安装包

```bash
composer require hua5p/hyperf-logstash
```

### 2. 配置环境变量

在 `.env` 文件中添加：

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

### 3. 重启服务

```bash
php bin/hyperf.php server:start
```

## 验证安装

### 1. 检查进程

```bash
# 检查 Logstash 消费进程是否启动
ps aux | grep logstash-queue-consumer
```

### 2. 测试日志

```php
<?php

use Hyperf\Logger\LoggerFactory;

// 在任何服务中测试
$logger = $this->loggerFactory->get('default');
$logger->info('测试 Logstash 集成', ['test' => true]);
```

### 3. 检查队列

```bash
# 检查 Redis 队列
redis-cli llen queue:logstash
```

## 配置选项

### 环境变量

| 变量名 | 默认值 | 说明 |
|--------|--------|------|
| LOGSTASH_ENABLED | false | 是否启用 Logstash 集成 |
| LOGSTASH_HOST | 192.168.31.210 | Logstash 服务地址 |
| LOGSTASH_PORT | 5000 | Logstash 服务端口 |
| LOGSTASH_PROJECT | hua5Rec | 项目名称 |
| LOGSTASH_TEAM | hua5p | 团队名称 |

### 自定义配置

如果需要自定义配置，可以在 `config/autoload/logger.php` 中覆盖：

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

## 故障排除

### 1. 进程未启动

检查 `LOGSTASH_ENABLED` 环境变量是否正确设置：

```bash
echo $LOGSTASH_ENABLED
```

### 2. 连接失败

检查 Logstash 服务状态：

```bash
telnet 192.168.31.210 5000
```

### 3. 队列消息堆积

检查 Redis 连接和进程状态：

```bash
redis-cli ping
ps aux | grep logstash-queue-consumer
```

## 升级指南

### 从旧版本升级

1. 更新包版本：

```bash
composer update hua5p/hyperf-logstash
```

2. 清除缓存：

```bash
rm -rf runtime/container
```

3. 重启服务：

```bash
php bin/hyperf.php server:start
```

## 卸载

### 1. 移除包

```bash
composer remove hua5p/hyperf-logstash
```

### 2. 清理配置

删除相关的环境变量和配置文件。

### 3. 重启服务

```bash
php bin/hyperf.php server:start
``` 