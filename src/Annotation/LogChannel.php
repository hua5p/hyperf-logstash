<?php

namespace Hua5p\HyperfLogstash\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

#[Attribute(Attribute::TARGET_CLASS)]
class LogChannel extends AbstractAnnotation
{
    public function __construct(
        public string $module = 'default',
        public string $type = 'app'
    ) {}
}
