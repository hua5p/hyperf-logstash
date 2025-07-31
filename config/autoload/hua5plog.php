<?php

declare(strict_types=1);

use Hua5p\HyperfLogstash\Logger\LogstashQueueHandler;
use Hua5p\HyperfLogstash\Process\LogstashQueueConsumer;
use Hua5p\HyperfLogstash\Aspect\LogChannelAspect;
use Hua5p\HyperfLogstash\Aspect\HLoggerAspect;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use function Hyperf\Support\env;

return [
    // Logstash 连接配置
    'logstash' => [
        'host' => env('LOGSTASH_HOST', '192.168.31.210'),
        'port' => env('LOGSTASH_PORT', 5000),
        'project' => env('LOGSTASH_PROJECT', 'hua5Rec'),
        'team' => env('LOGSTASH_TEAM', 'hua5p'),
        'enabled' => env('LOGSTASH_ENABLED', true),
        'max_files' => 7,
        'date_format' => 'Y-m-d H:i:s',
    ],

    // 日志配置
    'logger' => [
        'default' => [
            'handlers' => [
                [
                    'class' => RotatingFileHandler::class,
                    'constructor' => [
                        'filename' => dirname(__DIR__, 2) . '/runtime/logs/hyperf.log',
                        'level' => Level::Debug,
                    ],
                    'formatter' => [
                        'class' => LineFormatter::class,
                        'constructor' => [
                            'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                            'dateFormat' => 'Y-m-d H:i:s',
                            'allowInlineLineBreaks' => true,
                        ],
                    ],
                ],
                // Logstash 处理器
                [
                    'class' => LogstashQueueHandler::class,
                    'constructor' => [
                        'host' => env('LOGSTASH_HOST', '192.168.31.210'),
                        'port' => env('LOGSTASH_PORT', 5000),
                        'project' => env('LOGSTASH_PROJECT', 'hua5Rec'),
                        'module' => 'default',
                        'team' => env('LOGSTASH_TEAM', 'hua5p'),
                        'level' => Level::Info,
                    ],
                    'formatter' => [
                        'class' => JsonFormatter::class,
                        'constructor' => [
                            'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                            'dateFormat' => 'Y-m-d H:i:s',
                            'allowInlineLineBreaks' => true,
                        ],
                    ],
                ],
            ],
            'processor' => [
                'class' => \Hua5p\HyperfLogstash\Logger\UuidRequestIdProcessor::class,
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

    // 自动注册的依赖
    'dependencies' => [
        \Hua5p\HyperfLogstash\Service\LogFactoryService::class => \Hua5p\HyperfLogstash\Service\LogFactoryService::class,
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
