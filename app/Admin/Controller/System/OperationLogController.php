<?php

declare(strict_types=1);

namespace App\Admin\Controller\System;

use App\Admin\Controller\BaseController;
use App\Admin\Middleware\AdminAuthMiddleware;
use App\Admin\Middleware\PermissionMiddleware;
use App\Common\Annotation\Permission;
use App\Common\Service\OperationLogService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/api/system/operation-logs')]
#[Middlewares([AdminAuthMiddleware::class, PermissionMiddleware::class])]
class OperationLogController extends BaseController
{
    #[Inject]
    protected OperationLogService $operationLogService;

    /**
     * 获取操作日志列表
     * GET /api/system/operation-logs
     */
    #[GetMapping(path: '')]
    #[Permission(code: 'system:operation-log:list')]
    public function list(): ResponseInterface
    {
        $result = $this->operationLogService->getList($this->getParams());
        return $this->response->paginate(
            $result['records'],
            $result['current'],
            $result['size'],
            $result['total']
        );
    }
}
