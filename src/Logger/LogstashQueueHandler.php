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
    private string $machineName;

    public function __construct(
        string $host = '192.168.210',
        int|string $port = 5000,
        string $project = 'hua5p',
        string $module = 'default',
        string $team = 'hua5p',
        string $machineName = '',
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
        $this->machineName = $this->getMachineName($machineName);

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
            //消息体中增加机器名称
            $data['machine_name'] = $this->machineName;

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

    /**
     * 获取可读的机器名称
     */
    private function getMachineName(string $customName = ''): string
    {
        // 优先使用自定义名称
        if (!empty($customName)) {
            return $customName;
        }

        // 尝试从环境变量获取
        $envMachineName = $_ENV['MACHINE_NAME'] ?? $_SERVER['MACHINE_NAME'] ?? getenv('MACHINE_NAME');
        if (!empty($envMachineName)) {
            return $envMachineName;
        }

        // 尝试从环境变量获取服务器标识
        $serverId = $_ENV['SERVER_ID'] ?? $_SERVER['SERVER_ID'] ?? getenv('SERVER_ID');
        if (!empty($serverId)) {
            return "server-{$serverId}";
        }

        // 尝试获取容器名称（Docker环境）
        $containerName = $_ENV['HOSTNAME'] ?? $_SERVER['HOSTNAME'] ?? getenv('HOSTNAME');
        if (!empty($containerName) && $containerName !== gethostname()) {
            return $containerName;
        }

        // 尝试获取IP地址作为标识
        $ip = $this->getLocalIp();
        if (!empty($ip)) {
            return "server-{$ip}";
        }

        // 最后使用主机名
        return gethostname() ?: 'unknown-server';
    }

    /**
     * 获取本机IP地址
     */
    private function getLocalIp(): string
    {
        // 尝试获取本机IP
        $ips = [];

        // 获取所有网络接口
        if (function_exists('net_get_interfaces')) {
            $interfaces = net_get_interfaces();
            foreach ($interfaces as $interface) {
                if (isset($interface['unicast'])) {
                    foreach ($interface['unicast'] as $unicast) {
                        if (
                            isset($unicast['address']) &&
                            filter_var($unicast['address'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
                        ) {
                            $ips[] = $unicast['address'];
                        }
                    }
                }
            }
        }

        // 如果没有公网IP，尝试获取内网IP
        if (empty($ips)) {
            $localIp = $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? '';
            if (!empty($localIp) && filter_var($localIp, FILTER_VALIDATE_IP)) {
                return $localIp;
            }
        }

        return $ips[0] ?? '';
    }
}
