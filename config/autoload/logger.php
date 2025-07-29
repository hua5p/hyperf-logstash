<?php

declare(strict_types=1);

use Hua5p\HyperfLogstash\Logger\LogstashQueueHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;

return [
    'default' => [
        'handlers' => [
            [
                'class' => RotatingFileHandler::class,
                'constructor' => [
                    'filename' => BASE_PATH . '/runtime/logs/hyperf.log',
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
            // 添加 Logstash 处理器
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
                    'class' => \Monolog\Formatter\JsonFormatter::class,
                    'constructor' => [
                        'format' => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                        'dateFormat' => 'Y-m-d H:i:s',
                        'allowInlineLineBreaks' => true,
                    ],
                ],
            ],
        ],
        'processor' => [
            'class' => \Mine\Support\Logger\UuidRequestIdProcessor::class,
        ],
    ],
];
