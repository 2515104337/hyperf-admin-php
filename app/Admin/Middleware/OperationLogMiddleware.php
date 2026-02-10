<?php

declare(strict_types=1);

namespace App\Admin\Middleware;

use App\Common\Service\OperationLogService;
use Hyperf\Context\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class OperationLogMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected OperationLogService $operationLogService
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Skip GET requests
        if ($request->getMethod() === 'GET') {
            return $handler->handle($request);
        }

        $method = $request->getMethod();
        $path = $request->getUri()->getPath();
        $params = $request->getParsedBody();
        $ip = $this->getClientIp($request);
        $userAgent = $request->getHeaderLine('User-Agent');

        $response = $handler->handle($request);

        // After response, AdminAuthMiddleware has set Context
        $userId = Context::get('admin_user_id');
        if (!$userId) {
            return $response;
        }

        $logData = [
            'user_id' => $userId,
            'username' => Context::get('admin_username', ''),
            'method' => $method,
            'path' => $path,
            'params' => json_encode($params),
            'status' => $response->getStatusCode(),
            'ip' => $ip,
            'user_agent' => $userAgent,
            'module' => $this->extractModule($path),
            'description' => $this->generateDescription($method, $path),
            'before_data' => null,
            'after_data' => null,
        ];

        $this->operationLogService->pushToQueue($logData);

        return $response;
    }

    private function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        if (isset($serverParams['http_x_forwarded_for'])) {
            return explode(',', $serverParams['http_x_forwarded_for'])[0];
        }

        if (isset($serverParams['http_x_real_ip'])) {
            return $serverParams['http_x_real_ip'];
        }

        return $serverParams['remote_addr'] ?? '';
    }

    private function extractModule(string $path): string
    {
        // Extract module from path like /api/system/users -> system
        $parts = explode('/', trim($path, '/'));
        return $parts[1] ?? 'unknown';
    }

    private function generateDescription(string $method, string $path): string
    {
        $action = match($method) {
            'POST' => '创建',
            'PUT', 'PATCH' => '更新',
            'DELETE' => '删除',
            default => '操作',
        };

        $resource = basename($path);
        return "{$action} {$resource}";
    }
}
