<?php

declare(strict_types=1);

namespace App\Admin\Controller\System;

use App\Admin\Controller\BaseController;
use App\Admin\Middleware\AdminAuthMiddleware;
use App\Admin\Middleware\PermissionMiddleware;
use App\Admin\Request\System\RoleCreateRequest;
use App\Admin\Request\System\RoleUpdateMenusRequest;
use App\Admin\Request\System\RoleUpdateRequest;
use App\Common\Annotation\Permission;
use App\Common\Service\System\RoleService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/api/role')]
#[Middlewares([AdminAuthMiddleware::class, PermissionMiddleware::class])]
class RoleController extends BaseController
{
    #[Inject]
    protected RoleService $roleService;

    /**
     * 获取角色列表
     * GET /api/role/list
     */
    #[GetMapping(path: 'list')]
    public function list(): ResponseInterface
    {
        $result = $this->roleService->getList($this->getParams());
        return $this->response->success($result);
    }

    /**
     * 获取单个角色详情
     * GET /api/role/{id}
     */
    #[GetMapping(path: '{id:\d+}')]
    public function show(int $id): ResponseInterface
    {
        $role = $this->roleService->getById($id);
        return $this->response->success($role);
    }

    /**
     * 获取所有角色（不分页，用于下拉选择）
     * GET /api/role/all
     */
    #[GetMapping(path: 'all')]
    public function all(): ResponseInterface
    {
        $result = $this->roleService->getAll();
        return $this->response->success($result);
    }

    /**
     * 创建角色
     * POST /api/role
     */
    #[PostMapping(path: '')]
    #[Permission(code: 'role:add')]
    public function create(RoleCreateRequest $form): ResponseInterface
    {
        $role = $this->roleService->create($form->validatedData());
        return $this->response->success(['id' => $role->id], '创建成功');
    }

    /**
     * 更新角色
     * PUT /api/role/{id}
     */
    #[PutMapping(path: '{id}')]
    #[Permission(code: 'role:edit')]
    public function update(int $id, RoleUpdateRequest $form): ResponseInterface
    {
        $this->roleService->update($id, $form->validatedData());
        return $this->response->success(null, '更新成功');
    }

    /**
     * 删除角色
     * DELETE /api/role/{id}
     */
    #[DeleteMapping(path: '{id}')]
    #[Permission(code: 'role:delete')]
    public function delete(int $id): ResponseInterface
    {
        $this->roleService->delete($id);
        return $this->response->success(null, '删除成功');
    }

    /**
     * 获取角色菜单权限
     * GET /api/role/menus/{id}
     */
    #[GetMapping(path: 'menus/{id}')]
    public function getMenus(int $id): ResponseInterface
    {
        $menuIds = $this->roleService->getMenuIds($id);
        return $this->response->success($menuIds);
    }

    /**
     * 更新角色菜单权限
     * PUT /api/role/menus/{id}
     */
    #[PutMapping(path: 'menus/{id}')]
    #[Permission(code: 'role:edit')]
    public function updateMenus(int $id, RoleUpdateMenusRequest $form): ResponseInterface
    {
        $data = $form->validatedData();
        $this->roleService->updateMenus($id, $data['menuIds']);
        return $this->response->success(null, '菜单权限更新成功');
    }
}
