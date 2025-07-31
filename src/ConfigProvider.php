<?php

declare(strict_types=1);

namespace Hua5p\HyperfLogstash;

use Hua5p\HyperfLogstash\Aspect\LogChannelAspect;
use Hua5p\HyperfLogstash\Aspect\HLoggerAspect;
use Hua5p\HyperfLogstash\Process\LogstashQueueConsumer;
use Hua5p\HyperfLogstash\Service\LogFactoryService;

class ConfigProvider
{
    public function __invoke(): array
    {
        $config = require __DIR__ . '/../config/autoload/logstash.php';

        return [
            // 自动注册配置
            'dependencies' => $config['dependencies'] ?? [],
            'aspects' => $config['aspects'] ?? [],
            'processes' => $config['processes'] ?? [],
            'annotations' => $config['annotations'] ?? [],
            // 发布配置文件
            'publish' => [
                [
                    'id' => 'logstash',
                    'description' => 'The logstash config for hyperf-logstash.',
                    'source' => __DIR__ . '/../config/autoload/logstash.php',
                    'destination' => 'config/autoload/logstash.php',
                ],
            ],
        ];
    }
}
