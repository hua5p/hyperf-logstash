<?php

declare(strict_types=1);

namespace Hua5p\HyperfLogstash\Process;

use Hyperf\Config\Annotation\Value;
use Hyperf\Process\AbstractProcess;
use Hyperf\Redis\RedisFactory;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use function Hyperf\Support\env;

/**
 * Logstash 队列消费进程
 * 
 * 注意：此进程需要在主应用的 config/autoload/processes.php 中注册：
 * 
 * return [
 *     Hua5p\HyperfLogstash\Process\LogstashQueueConsumer::class,
 * ];
 * 
 * 或者使用配置方式：
 * 
 * return [
 *     [
 *         'name' => 'logstash-queue-consumer',
 *         'class' => Hua5p\HyperfLogstash\Process\LogstashQueueConsumer::class,
 *         'nums' => 2,
 *     ],
 * ];
 */
class LogstashQueueConsumer extends AbstractProcess
{
    private string $queueKey = 'queue:logstash';
    private string $failedQueueKey = 'queue:logstash:failed';
    private string $delayedQueueKey = 'queue:logstash:delayed';
    private int $timeout = 5;
    private int $maxRetry = 3;
    private Logger $processLogger;

    #[Value('logstash.host')]
    private ?string $host = null;

    #[Value('logstash.port')]
    private ?int $port = null;

    public bool $enableCoroutine = true;
    public bool $listening = true;

    public function __construct(
        ContainerInterface $container,
        private RedisFactory $redisFactory,
        private LoggerFactory $loggerFactory,
    ) {
        parent::__construct($container);

        // Set default values, in case config injection fails
        if ($this->host === null) {
            $this->host = env('LOGSTASH_HOST', '192.168.31.210');
        }
        if ($this->port === null) {
            $this->port = (int) env('LOGSTASH_PORT', 5000);
        }

        // 创建专门用于进程内部日志的 logger，避免队列循环
        $this->processLogger = $this->createProcessLogger();
    }

    /**
     * 创建进程内部专用的 logger，只写入文件，不发送到队列
     */
    private function createProcessLogger(): Logger
    {
        $logger = new Logger('logstash-queue-consumer');

        // 只使用文件处理器，避免队列循环
        $handler = new StreamHandler(
            'runtime/logs/logstash-consumer.log',
            \Monolog\Level::Info
        );

        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            'Y-m-d H:i:s'
        );

        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        return $logger;
    }

    public function handle(): void
    {
        $redis = $this->redisFactory->get('default');

        $this->processLogger->info('Logstash 队列消费进程启动', [
            'queue_key' => $this->queueKey,
            'host' => $this->host,
            'port' => $this->port,
            'process_id' => getmypid(),
        ]);

        while (true) {
            try {
                $result = $redis->brpop($this->queueKey, $this->timeout);

                if ($result === null || empty($result)) {
                    sleep(1);
                    continue;
                }

                $message = $result[1];
                $jobData = $this->parseMessage($message);

                if ($jobData === null) {
                    $this->processLogger->warning('消息格式无效，跳过处理', ['message' => $message]);
                    continue;
                }

                $this->sendToLogstash($jobData);
            } catch (\Throwable $e) {
                $this->processLogger->error('处理 Logstash 消息异常', [
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                sleep(1);
            }
        }

        $this->processLogger->info('Logstash 队列消费进程停止');
    }

    private function parseMessage(string $message): ?array
    {
        try {
            $data = json_decode($message, true, 512, JSON_THROW_ON_ERROR);

            $requiredFields = ['message', 'host', 'port'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    return null;
                }
            }

            return $data;
        } catch (\JsonException $e) {
            return null;
        }
    }

    private function sendToLogstash(array $jobData): void
    {
        try {
            $startTime = microtime(true);

            $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
            if ($socket === false) {
                throw new \RuntimeException("Failed to create socket");
            }

            socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 10, 'usec' => 0]);
            socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 10, 'usec' => 0]);

            $result = socket_connect($socket, $jobData['host'], $jobData['port']);
            if ($result === false) {
                $error = socket_last_error($socket);
                $errorMsg = socket_strerror($error);
                socket_close($socket);
                throw new \RuntimeException("Failed to connect to Logstash: {$errorMsg} (错误码: {$error})");
            }

            $message = $jobData['message'] . "\n";
            $bytesWritten = socket_write($socket, $message, strlen($message));
            socket_close($socket);

            if ($bytesWritten === false || $bytesWritten === 0) {
                throw new \RuntimeException("Failed to write log message to Logstash");
            }

            $processingTime = (microtime(true) - $startTime) * 1000;
            $this->processLogger->info('Logstash 消息发送成功', [
                'host' => $jobData['host'],
                'port' => $jobData['port'],
                'bytes_written' => $bytesWritten,
                'processing_time_ms' => round($processingTime, 2)
            ]);
        } catch (\Throwable $e) {
            $this->processLogger->error('发送到 Logstash 失败', [
                'host' => $jobData['host'],
                'port' => $jobData['port'],
                'exception' => $e->getMessage()
            ]);
            $this->handleFailedMessage($jobData, $e);
        }
    }

    private function handleFailedMessage(array $jobData, \Throwable $exception): void
    {
        $redis = $this->redisFactory->get('default');

        try {
            $failedMessage = [
                'original_data' => $jobData,
                'error_message' => $exception->getMessage(),
                'failed_at' => date('Y-m-d H:i:s'),
                'retry_count' => 0,
            ];

            $redis->lpush($this->failedQueueKey, json_encode($failedMessage));

            $this->processLogger->warning('Logstash 消息发送失败，已移至失败队列', [
                'host' => $jobData['host'],
                'port' => $jobData['port']
            ]);
        } catch (\Throwable $e) {
            $this->processLogger->error('处理失败消息时出错', [
                'host' => $jobData['host'],
                'original_exception' => $exception->getMessage(),
                'dead_letter_exception' => $e->getMessage()
            ]);
        }
    }

    public function isEnable($server): bool
    {
        // 如果没有设置 LOGSTASH_ENABLED，默认启用（向后兼容）
        $enabled = env('LOGSTASH_ENABLED', true);

        // 如果明确设置为 false，则禁用
        if ($enabled === false || $enabled === 'false' || $enabled === '0') {
            return false;
        }

        return true;
    }

    /**
     * 获取进程日志器（仅用于测试）
     */
    public function getProcessLogger(): Logger
    {
        return $this->processLogger;
    }
}
