# Hyperf Logstash 包总结

## 📦 包信息

- **包名**: `hua5p/hyperf-logstash`
- **版本**: 1.0.0
- **许可证**: MIT
- **PHP 版本**: >= 8.1
- **Hyperf 版本**: ^3.0

## 🎯 功能特性

### ✅ 核心功能
- **自动集成**: 安装后自动为所有日志添加 Logstash 处理器
- **异步处理**: 通过 Redis 队列异步发送日志，不影响主业务流程
- **多通道支持**: 支持不同日志通道生成不同索引
- **注解日志**: 支持 `#[LogChannel]` 注解自动注入日志实例
- **失败重试**: 自动处理发送失败的消息
- **配置灵活**: 支持环境变量和配置文件配置
- **零侵入**: 无需修改现有代码

### 🔧 技术实现
- **LogstashQueueHandler**: Monolog 处理器，格式化日志并推送到队列
- **LogstashQueueConsumer**: Hyperf 进程，消费队列并发送到 Logstash
- **LogChannelAspect**: 切面处理器，自动注入日志实例
- **LogFactoryService**: 日志工厂服务，创建和管理日志实例
- **BaseService**: 基础服务类，提供日志实例的自动注入
- **配置自动加载**: 通过 Hyperf 的配置系统自动加载
- **类型安全**: 支持 `Monolog\Level` 和 `int` 类型参数

## 📁 文件结构

```
hyperf-logstash/
├── src/
│   ├── Annotation/
│   │   └── LogChannel.php               # 日志通道注解
│   ├── Aspect/
│   │   └── LogChannelAspect.php         # 日志注入切面
│   ├── Logger/
│   │   ├── LogstashQueueHandler.php     # 日志处理器
│   │   └── UuidRequestIdProcessor.php   # 请求ID处理器
│   ├── Process/
│   │   └── LogstashQueueConsumer.php    # 队列消费进程
│   └── Service/
│       ├── LogFactoryService.php        # 日志工厂服务
│       └── BaseService.php              # 基础服务类
├── config/autoload/
│   ├── logger.php                       # 日志配置
│   ├── processes.php                    # 进程配置
│   └── aspects.php                      # 切面配置
├── tests/
│   └── LogstashQueueHandlerTest.php     # 单元测试
├── docs/
│   └── installation.md                  # 安装指南
├── examples/
│   ├── basic-usage.php                  # 基本使用示例
│   └── annotation-usage.php             # 注解使用示例
├── scripts/
│   └── build.sh                         # 构建脚本
├── composer.json                        # 包配置
├── phpunit.xml                          # 测试配置
├── README.md                           # 主文档
└── LICENSE                             # 许可证
```

## 🚀 使用方法

### 1. 安装
```bash
composer require hua5p/hyperf-logstash
```

### 2. 配置环境变量
```env
LOGSTASH_ENABLED=true
LOGSTASH_HOST=192.168.31.210
LOGSTASH_PORT=5000
LOGSTASH_PROJECT=your-project
LOGSTASH_TEAM=your-team
```

### 3. 使用

#### 注解日志（推荐）
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
        $this->errorLogger->error('推荐失败', ['error' => '数据异常']);
    }
}
```

#### 自动生效
```php
// 所有日志自动写到 Logstash
$logger = $this->loggerFactory->get('default');
$logger->info('业务日志');
```

## 📊 日志索引格式

| 日志通道 | 索引格式 | 示例 |
|----------|----------|------|
| default | `{team}-{project}-default` | `hua5p-hua5rec-default` |
| sql | `{team}-{project}-sql` | `hua5p-hua5rec-sql` |
| other | `{team}-{project}-other` | `hua5p-hua5rec-other` |

## 🔧 配置选项

### 环境变量
| 变量名 | 默认值 | 说明 |
|--------|--------|------|
| LOGSTASH_ENABLED | false | 是否启用 Logstash 集成 |
| LOGSTASH_HOST | 192.168.31.210 | Logstash 服务地址 |
| LOGSTASH_PORT | 5000 | Logstash 服务端口 |
| LOGSTASH_PROJECT | hua5Rec | 项目名称 |
| LOGSTASH_TEAM | hua5p | 团队名称 |

### 自定义配置
支持在 `config/autoload/logger.php` 中覆盖默认配置。

## 🧪 测试

```bash
# 运行测试
composer test

# 构建包
./scripts/build.sh
```

## 📈 性能特点

- **异步处理**: 日志发送不阻塞主业务流程
- **队列缓冲**: 通过 Redis 队列缓冲日志消息
- **失败重试**: 自动处理网络异常和连接失败
- **内存优化**: 使用流式处理，避免内存溢出

## 🔍 监控和调试

### 队列监控
```bash
# 检查队列状态
redis-cli llen queue:logstash
redis-cli llen queue:logstash:failed
```

### 进程监控
```bash
# 检查消费进程
ps aux | grep logstash-queue-consumer
```

### 日志查看
```bash
# 查看进程日志
tail -f runtime/logs/hyperf.log | grep logstash
```

## 🛠️ 故障排除

### 常见问题
1. **进程未启动**: 检查 `LOGSTASH_ENABLED` 环境变量
2. **连接失败**: 检查 Logstash 服务状态和网络连接
3. **队列堆积**: 检查 Redis 连接和消费进程状态

### 调试步骤
1. 检查环境变量配置
2. 验证 Logstash 服务状态
3. 检查 Redis 连接
4. 查看进程日志

## 📚 文档

- [README.md](README.md) - 主文档
- [docs/installation.md](docs/installation.md) - 安装指南
- [examples/basic-usage.php](examples/basic-usage.php) - 使用示例

## 🤝 贡献

欢迎提交 Issue 和 Pull Request！

## 📄 许可证

MIT License - 详见 [LICENSE](LICENSE) 文件

---

**总结**: 这是一个功能完整、易于使用的 Hyperf Logstash 集成包，提供了零侵入的日志集中化管理解决方案。 