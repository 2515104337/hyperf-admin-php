<?php

declare(strict_types=1);

namespace App\Admin\Controller\System;

use App\Admin\Controller\BaseController;
use App\Admin\Middleware\AdminAuthMiddleware;
use App\Admin\Middleware\PermissionMiddleware;
use App\Common\Annotation\Permission;
use App\Common\Service\LoginLogService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/api/system/login-logs')]
#[Middlewares([AdminAuthMiddleware::class, PermissionMiddleware::class])]
class LoginLogController extends BaseController
{
    #[Inject]
    protected LoginLogService $loginLogService;

    /**
     * 获取登录日志列表
     * GET /api/system/login-logs
     */
    #[GetMapping(path: '')]
    #[Permission(code: 'system:login-log:list')]
    public function list(): ResponseInterface
    {
        $result = $this->loginLogService->getList($this->getParams());
        return $this->response->paginate(
            $result['records'],
            $result['current'],
            $result['size'],
            $result['total']
        );
    }
}
