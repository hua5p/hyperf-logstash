# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
- **UuidRequestIdProcessor**: Custom request ID processor

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