# 日志模式配置

Hyperf Logstash 包支持多种日志模式，可以根据不同的部署环境和需求进行配置。

## 配置选项

### 环境变量

| 变量名 | 默认值 | 说明 |
|--------|--------|------|
| `LOGSTASH_ENABLED` | `false` | 是否启用 Logstash 功能 |
| `LOGSTASH_DISABLE_LOCAL_LOGS` | `false` | 是否禁用本地文件日志 |

## 日志模式

### 1. 默认模式（推荐）

**配置：**
```env
LOGSTASH_ENABLED=true
LOGSTASH_DISABLE_LOCAL_LOGS=false
```

**特点：**
- ✅ 同时写入本地文件和 Logstash
- ✅ 本地文件便于调试和故障排查
- ✅ Logstash 便于集中日志管理和分析
- ✅ 双重保障，数据不丢失

**适用场景：**
- 开发环境
- 测试环境
- 生产环境（需要本地日志备份）

### 2. Logstash 专用模式

**配置：**
```env
LOGSTASH_ENABLED=true
LOGSTASH_DISABLE_LOCAL_LOGS=true
```

**特点：**
- ✅ 只写入 Logstash，不写本地文件
- ✅ 节省磁盘空间
- ✅ 减少 I/O 操作，提升性能
- ✅ 集中化日志管理

**适用场景：**
- 容器化部署（如 Docker、Kubernetes）
- 磁盘空间有限的服务器
- 高并发场景，需要优化 I/O 性能
- 已有完善的日志收集体系

### 3. 本地文件模式

**配置：**
```env
LOGSTASH_ENABLED=false
LOGSTASH_DISABLE_LOCAL_LOGS=false
```

**特点：**
- ✅ 只写入本地文件
- ✅ 不依赖外部服务
- ✅ 简单可靠

**适用场景：**
- 单机部署
- 网络环境不稳定
- 不需要集中日志管理

### 4. 禁用模式

**配置：**
```env
LOGSTASH_ENABLED=false
LOGSTASH_DISABLE_LOCAL_LOGS=true
```

**特点：**
- ⚠️ 不写入任何日志
- ⚠️ 仅用于特殊测试场景

**适用场景：**
- 性能测试
- 特殊调试场景

## 配置示例

### Docker 环境

```dockerfile
# Dockerfile
ENV LOGSTASH_ENABLED=true
ENV LOGSTASH_DISABLE_LOCAL_LOGS=true
ENV LOGSTASH_HOST=logstash.example.com
ENV LOGSTASH_PORT=5000
ENV LOGSTASH_PROJECT=myapp
ENV LOGSTASH_TEAM=devops
```

### Kubernetes 环境

```yaml
# deployment.yaml
env:
- name: LOGSTASH_ENABLED
  value: "true"
- name: LOGSTASH_DISABLE_LOCAL_LOGS
  value: "true"
- name: LOGSTASH_HOST
  value: "logstash-service"
- name: LOGSTASH_PORT
  value: "5000"
```

### 传统服务器环境

```bash
# /etc/environment 或 ~/.bashrc
export LOGSTASH_ENABLED=true
export LOGSTASH_DISABLE_LOCAL_LOGS=false
export LOGSTASH_HOST=192.168.1.100
export LOGSTASH_PORT=5000
```

## 性能对比

| 模式 | 磁盘 I/O | 网络 I/O | 内存占用 | 可靠性 |
|------|----------|----------|----------|--------|
| 默认模式 | 高 | 高 | 中 | 最高 |
| Logstash 专用 | 无 | 高 | 低 | 高 |
| 本地文件 | 高 | 无 | 中 | 高 |
| 禁用模式 | 无 | 无 | 最低 | 无 |

## 最佳实践

### 1. 环境配置建议

- **开发环境**：使用默认模式，便于调试
- **测试环境**：使用默认模式，便于问题排查
- **生产环境**：根据部署方式选择
  - 容器化：推荐 Logstash 专用模式
  - 传统服务器：推荐默认模式

### 2. 监控建议

- 监控 Redis 队列长度：`redis-cli llen queue:logstash`
- 监控 Logstash 连接状态
- 监控磁盘空间使用情况
- 设置日志文件大小告警

### 3. 故障排查

#### 本地文件模式故障排查
```bash
# 检查日志文件
tail -f runtime/logs/hyperf.log
tail -f runtime/logs/{module}/{type}.log

# 检查磁盘空间
df -h
```

#### Logstash 模式故障排查
```bash
# 检查 Redis 队列
redis-cli llen queue:logstash
redis-cli lrange queue:logstash 0 10

# 检查 Logstash 连接
telnet logstash-host logstash-port

# 检查消费进程
php bin/hyperf.php process:list
```

## 测试验证

运行测试脚本验证配置：

```bash
# 测试所有模式
php examples/logstash-only-test.php

# 测试协程并发
php examples/coroutine-test.php
```

## 迁移指南

### 从默认模式迁移到 Logstash 专用模式

1. **备份现有日志**
```bash
cp -r runtime/logs runtime/logs.backup
```

2. **更新环境变量**
```env
LOGSTASH_DISABLE_LOCAL_LOGS=true
```

3. **重启应用**
```bash
php bin/hyperf.php server:stop
php bin/hyperf.php server:start
```

4. **验证配置**
```bash
php examples/logstash-only-test.php
```

### 从 Logstash 专用模式迁移到默认模式

1. **更新环境变量**
```env
LOGSTASH_DISABLE_LOCAL_LOGS=false
```

2. **重启应用**
```bash
php bin/hyperf.php server:stop
php bin/hyperf.php server:start
```

3. **验证配置**
```bash
php examples/logstash-only-test.php
```

## 注意事项

1. **配置生效**：修改环境变量后需要重启应用
2. **缓存清理**：如果使用配置缓存，需要清理缓存
3. **权限检查**：确保应用有写入日志目录的权限
4. **网络连接**：Logstash 模式需要确保网络连接稳定
5. **队列监控**：定期监控 Redis 队列状态，避免队列积压
