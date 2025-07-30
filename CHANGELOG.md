# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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