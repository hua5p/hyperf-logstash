<?php

declare(strict_types=1);

namespace Hua5p\HyperfLogstash;

use Hua5p\HyperfLogstash\Aspect\LogChannelAspect;
use Hua5p\HyperfLogstash\Process\LogstashQueueConsumer;
use Hua5p\HyperfLogstash\Service\LogFactoryService;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            // 自动注册配置
            'dependencies' => [
                LogFactoryService::class => LogFactoryService::class,
            ],
            'aspects' => [
                LogChannelAspect::class,
            ],
            'processes' => [
                LogstashQueueConsumer::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            // 发布配置文件
            'publish' => [
                [
                    'id' => 'config',
                    'description' => 'The config for hyperf-logstash.',
                    'source' => __DIR__ . '/../config/autoload/logger.php',
                    'destination' => 'config/autoload/logger.php',
                ],
                [
                    'id' => 'processes',
                    'description' => 'The processes config for hyperf-logstash.',
                    'source' => __DIR__ . '/../config/autoload/processes.php',
                    'destination' => 'config/autoload/processes.php',
                ],
                [
                    'id' => 'aspects',
                    'description' => 'The aspects config for hyperf-logstash.',
                    'source' => __DIR__ . '/../config/autoload/aspects.php',
                    'destination' => 'config/autoload/aspects.php',
                ],
            ],
        ];
    }
}
