<?php

declare(strict_types=1);

namespace App\Common\Service;

use App\Common\Model\System\OperationLog;
use App\Job\OperationLogJob;
use Hyperf\AsyncQueue\Driver\DriverFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class OperationLogService
{
    public function __construct(
        protected ContainerInterface $container
    ) {}

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function pushToQueue(array $data): void
    {
        $data = $this->maskSensitiveData($data);
        $this->container->get(DriverFactory::class)->get('default')->push(new OperationLogJob($data));
    }

    public function getList(array $params): array
    {
        $page = (int) ($params['page'] ?? 1);
        $pageSize = (int) ($params['page_size'] ?? 20);

        $query = OperationLog::filter($params)->orderBy('id', 'desc');
        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)->limit($pageSize)->get()->toArray();

        return [
            'records' => $list,
            'current' => $page,
            'size' => $pageSize,
            'total' => $total,
        ];
    }

    private function maskSensitiveData(array $data): array
    {
        if (isset($data['params'])) {
            $params = is_string($data['params']) ? json_decode($data['params'], true) : $data['params'];

            if (is_array($params)) {
                $sensitiveFields = ['password', 'token', 'secret', 'api_key', 'access_token'];

                foreach ($sensitiveFields as $field) {
                    if (isset($params[$field])) {
                        $params[$field] = '[REDACTED]';
                    }
                }

                $data['params'] = json_encode($params);
            }
        }

        return $data;
    }
}
