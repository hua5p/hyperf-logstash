<?php

declare(strict_types=1);

namespace Hua5p\HyperfLogstash\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_METHOD)]
class HLogger extends AbstractAnnotation
{
    public function __construct(
        public string $message = '',
        public string $level = 'info',
        public array $context = [],
        public bool $logParams = true,
        public bool $logResult = false,
        public bool $logException = true,
        public bool $logPerformance = false,
        public ?string $module = null,
        public string $type = 'app'
    ) {}
}
