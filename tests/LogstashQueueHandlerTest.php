<?php

declare(strict_types=1);

namespace Hua5p\HyperfLogstash\Tests;

use Hua5p\HyperfLogstash\Logger\LogstashQueueHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class LogstashQueueHandlerTest extends TestCase
{
    public function testConstructorWithStringPort()
    {
        $handler = new LogstashQueueHandler(
            '192.168.1.1',
            '5000',
            'test-project',
            'test-module',
            'test-team'
        );

        $this->assertInstanceOf(LogstashQueueHandler::class, $handler);
    }

    public function testConstructorWithIntPort()
    {
        $handler = new LogstashQueueHandler(
            '192.168.1.1',
            5000,
            'test-project',
            'test-module',
            'test-team'
        );

        $this->assertInstanceOf(LogstashQueueHandler::class, $handler);
    }

    public function testConstructorWithMonologLevel()
    {
        $handler = new LogstashQueueHandler(
            '192.168.1.1',
            5000,
            'test-project',
            'test-module',
            'test-team',
            Logger::INFO
        );

        $this->assertInstanceOf(LogstashQueueHandler::class, $handler);
    }
}
