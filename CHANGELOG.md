# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.0.8] - 2025-01-29

### Added
- **Logstash 专用模式**: 支持只写 Logstash 不写本地文件的功能
- **环境变量配置**: 新增 `LOGSTASH_DISABLE_LOCAL_LOGS` 环境变量
- **日志模式测试**: 添加完整的日志模式测试用例
- **日志模式文档**: 提供详细的日志模式配置说明

### Changed
- **配置结构**: 在配置中添加 `disable_local_logs` 选项
- **安装文档**: 更新安装文档，说明日志模式配置
- **性能优化**: Logstash 专用模式可减少磁盘 I/O，提升性能

## [0.0.7] - 2025-01-29

### Fixed
- **协程并发问题**: 修复协程环境下 `#[LogChannel]` 注解的 "mkdir(): File exists" 错误
- **日志实例竞态条件**: 使用协程安全的单例模式，防止重复创建日志实例
- **目录创建冲突**: 添加重试机制处理并发目录创建

### Added
- **协程测试**: 添加协程并发测试用例验证修复效果
- **协程安全文档**: 提供详细的协程问题修复说明

## [0.0.6] - 2025-01-29

### Changed
- **UuidRequestIdProcessor**: 升级为使用 `ramsey/uuid` 库，参照 MineAdmin 实现
- **进程启用逻辑**: 默认启用进程，除非明确设置为 false
- **防循环优化**: 消费进程使用独立文件日志器，避免无限循环

### Added
- **ramsey/uuid**: 添加 UUID 生成依赖
- **协程上下文支持**: 支持 Hyperf 协程上下文传递 request_id
- **诊断工具**: 提供包诊断和故障排除工具

### Fixed
- **配置加载**: 修复 ConfigProvider 自动注册问题
- **BASE_PATH**: 修复包中 BASE_PATH 常量未定义问题
- **进程注册**: 修复进程自动注册问题

## [Unreleased]

### Added
- Initial release
- Global Logstash integration for all logs
- `#[LogChannel]` annotation for automatic logger injection
- Asynchronous queue processing with Redis
- Multi-channel support for different log indices
- Failure retry mechanism
- Flexible configuration via environment variables
- Zero-intrusion integration

### Features
- **LogstashQueueHandler**: Monolog handler for formatting and queueing logs
- **LogstashQueueConsumer**: Hyperf process for consuming and sending logs to Logstash
- **LogChannelAspect**: AOP aspect for automatic logger injection
- **LogFactoryService**: Service for creating and managing logger instances
- **BaseService**: Abstract base class providing logger injection
- **UuidRequestIdProcessor**: Custom request ID processor with ramsey/uuid

### Configuration
- Environment variables support
- Automatic configuration loading
- Customizable log indices
- Redis queue configuration

## [1.0.0] - 2025-01-29

### Added
- Initial release of Hyperf Logstash integration package
- Support for PHP 8.1+
- Hyperf 3.0+ compatibility
- Comprehensive documentation and examples
- Unit tests and build scripts

### Features
- Global log integration with Logstash
- Annotation-based logger injection
- Asynchronous queue processing
- Multi-channel log support
- Failure retry mechanism
- Flexible configuration options

### Documentation
- Complete README with usage examples
- Installation guide
- API documentation
- Troubleshooting guide
- Performance optimization tips 