# 安装指南

## 快速安装

### 1. 安装包

```bash
composer require hua5p/hyperf-logstash
```

### 2. 配置环境变量

在 `.env` 文件中添加：

```env
# 启用 Logstash 功能
LOGSTASH_ENABLED=true

# Logstash 服务器配置
LOGSTASH_HOST=192.168.31.210
LOGSTASH_PORT=5000

# 项目配置
LOGSTASH_PROJECT=your-project
LOGSTASH_TEAM=your-team
```

### 3. 启动应用

```bash
php bin/hyperf.php start
```

**完成！** 🎉 包会自动注册所有必要的配置。

## 自动配置说明

安装后，包会自动注册以下配置：

### 自动注册的组件

1. **进程注册**
   - `LogstashQueueConsumer` 进程会自动注册
   - 默认启动 2 个消费进程

2. **切面注册**
   - `LogChannelAspect` 切面会自动注册
   - 支持 `#[LogChannel]` 注解

3. **依赖注入**
   - `LogFactoryService` 服务会自动注册
   - 支持注解自动注入日志实例

4. **注解扫描**
   - 自动扫描包内的注解
   - 无需手动配置扫描路径

### 验证安装

启动应用后，可以通过以下命令验证：

```bash
# 查看进程列表
php bin/hyperf.php process:list

# 应该看到 logstash-queue-consumer 进程
```

## 手动配置（可选）

如果需要自定义配置，可以手动发布配置文件：

```bash
php bin/hyperf.php vendor:publish hua5p/hyperf-logstash
```

### 发布的配置文件

- `config/autoload/logger.php` - 日志配置
- `config/autoload/processes.php` - 进程配置
- `config/autoload/aspects.php` - 切面配置

## 故障排除

### 1. 进程未启动

检查环境变量：
```bash
# 确保设置了
LOGSTASH_ENABLED=true
```

### 2. 连接失败

检查 Logstash 服务：
```bash
# 测试连接
telnet 192.168.31.210 5000
```

### 3. 配置未生效

清除缓存：
```bash
php bin/hyperf.php config:clear
```

## 下一步

安装完成后，您可以：

1. **使用注解日志**：参考 [注解使用示例](annotation-usage.php)
2. **查看日志**：检查 `runtime/logs/` 目录
3. **监控队列**：使用 Redis 命令查看队列状态
4. **配置 Logstash**：参考 [Logstash 配置示例](../README.md#logstash-配置示例) 