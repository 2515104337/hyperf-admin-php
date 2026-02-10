<?php

declare(strict_types=1);

namespace App\Common\Service;

use App\Job\LoginLogJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Psr\Container\ContainerInterface;

class LoginLogService
{
    public function __construct(
        protected ContainerInterface $container
    ) {}

    public function recordLogin(array $data): void
    {
        $data['action'] = 'login';
        $this->parseDeviceInfo($data);
        $this->pushToQueue($data);
    }

    public function recordLogout(int $userId, string $username, string $ip, string $userAgent): void
    {
        $data = [
            'user_id' => $userId,
            'username' => $username,
            'action' => 'logout',
            'status' => 'success',
            'ip' => $ip,
            'user_agent' => $userAgent,
        ];

        $this->parseDeviceInfo($data);
        $this->pushToQueue($data);
    }

    public function getList(array $params): array
    {
        $page = (int) ($params['page'] ?? 1);
        $pageSize = (int) ($params['page_size'] ?? 20);

        $query = \App\Common\Model\System\LoginLog::filter($params)->orderBy('id', 'desc');
        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)->limit($pageSize)->get()->toArray();

        return [
            'records' => $list,
            'current' => $page,
            'size' => $pageSize,
            'total' => $total,
        ];
    }

    private function pushToQueue(array $data): void
    {
        $this->container->get(DriverFactory::class)->get('default')->push(new LoginLogJob($data));
    }

    private function parseDeviceInfo(array &$data): void
    {
        $userAgent = $data['user_agent'] ?? '';

        if (str_contains($userAgent, 'Chrome')) {
            $data['browser'] = 'Chrome';
        } elseif (str_contains($userAgent, 'Firefox')) {
            $data['browser'] = 'Firefox';
        } elseif (str_contains($userAgent, 'Safari')) {
            $data['browser'] = 'Safari';
        } elseif (str_contains($userAgent, 'Edge')) {
            $data['browser'] = 'Edge';
        } else {
            $data['browser'] = 'Unknown';
        }

        if (str_contains($userAgent, 'Windows')) {
            $data['os'] = 'Windows';
        } elseif (str_contains($userAgent, 'Mac')) {
            $data['os'] = 'macOS';
        } elseif (str_contains($userAgent, 'Linux')) {
            $data['os'] = 'Linux';
        } elseif (str_contains($userAgent, 'Android')) {
            $data['os'] = 'Android';
        } elseif (str_contains($userAgent, 'iOS') || str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) {
            $data['os'] = 'iOS';
        } else {
            $data['os'] = 'Unknown';
        }
    }
}
