<?php

namespace Hua5p\HyperfLogstash\Logger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use Monolog\Formatter\JsonFormatter;

class LogstashQueueHandler extends AbstractProcessingHandler
{
    private string $host;
    private int $port;
    private string $project;
    private string $module;
    private string $team;

    public function __construct(
        string $host = '192.168.210',
        int|string $port = 5000,
        string $project = 'hua5p',
        string $module = 'default',
        string $team = 'hua5p',
        int|\Monolog\Level $level = \Monolog\Logger::DEBUG,
        bool $bubble = true
    ) {
        // 转换 level 参数
        $levelValue = $level instanceof \Monolog\Level ? $level->value : $level;

        parent::__construct($levelValue, $bubble);

        $this->host = $host;
        $this->port = is_string($port) ? (int)$port : $port;
        $this->project = $project;
        $this->module = $module;
        $this->team = $team;

        $this->setFormatter(new JsonFormatter());
    }

    protected function write(LogRecord $record): void
    {
        try {
            // 格式化日志消息
            $json = $this->getFormatter()->format($record);

            // 解码为数组
            $data = json_decode($json, true);

            // 将 index 字段提升到根级别
            $data['index'] = "{$this->team}-{$this->project}-{$this->module}";

            // 重新编码为 JSON
            $message = json_encode($data, JSON_UNESCAPED_UNICODE);

            // 推送到队列
            $this->pushToQueue($message);
        } catch (\Throwable $e) {
            error_log("LogstashQueueHandler write error: " . $e->getMessage());
        }
    }

    private function pushToQueue(string $message): void
    {
        try {
            $redis = \Hyperf\Context\ApplicationContext::getContainer()
                ->get(\Hyperf\Redis\RedisFactory::class)
                ->get('default');

            $jobData = [
                'message' => $message,
                'host' => $this->host,
                'port' => $this->port,
                'timestamp' => time(),
            ];

            $redis->lpush('queue:logstash', json_encode($jobData));
        } catch (\Throwable $e) {
            error_log("Failed to push log to queue: " . $e->getMessage());
        }
    }
}
