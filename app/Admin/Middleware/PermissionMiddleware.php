<?php

declare(strict_types=1);

namespace App\Admin\Middleware;

use App\Common\Annotation\Permission;
use App\Common\Helper\ResponseHelper;
use App\Common\Service\System\PermissionCacheService;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\AnnotationCollector;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * 接口权限中间件
 * 检查用户是否有权限访问当前接口
 */
class PermissionMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected PermissionCacheService $permissionCacheService;

    public function __construct(
        protected ResponseHelper $response
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 获取当前路由对应的控制器和方法
        $dispatched = $request->getAttribute(\Hyperf\HttpServer\Router\Dispatched::class);
        if (!$dispatched || !$dispatched->handler) {
            return $handler->handle($request);
        }

        $callback = $dispatched->handler->callback;
        if (!is_array($callback) || count($callback) !== 2) {
            return $handler->handle($request);
        }

        [$controller, $method] = $callback;

        // 获取方法上的 Permission 注解
        $permission = AnnotationCollector::getClassMethodAnnotation($controller, $method)[Permission::class] ?? null;

        // 没有权限注解，直接放行
        if (!$permission || empty($permission->code)) {
            return $handler->handle($request);
        }

        // 获取当前用户ID
        $userId = Context::get('admin_user_id');
        if (!$userId) {
            return $this->response->forbidden('用户未登录');
        }

        // 从缓存获取用户权限信息
        $userPermissionData = $this->permissionCacheService->getUserPermissions($userId);
        if (!$userPermissionData) {
            return $this->response->forbidden('用户不存在');
        }

        // 超级管理员拥有所有权限
        if ($userPermissionData['is_super_admin']) {
            return $handler->handle($request);
        }

        // 检查用户是否有该权限
        if (!in_array($permission->code, $userPermissionData['permissions'])) {
            return $this->response->forbidden('没有操作权限');
        }

        return $handler->handle($request);
    }
}
