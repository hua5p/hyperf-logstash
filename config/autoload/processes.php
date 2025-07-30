<?php

declare(strict_types=1);

use Hua5p\HyperfLogstash\Process\LogstashQueueConsumer;

return [
    // 方式1：简单注册（使用默认配置）
    LogstashQueueConsumer::class,

    // 方式2：详细配置（可选，如果需要自定义进程名称和数量）
    // [
    //     'name' => 'logstash-queue-consumer',
    //     'class' => LogstashQueueConsumer::class,
    //     'nums' => 2,  // 进程数量
    //     'enable' => true,  // 是否启用
    // ],
];
