<?php

declare(strict_types=1);

namespace App\Admin\Controller\System;

use App\Admin\Controller\BaseController;
use App\Admin\Middleware\AdminAuthMiddleware;
use App\Admin\Middleware\PermissionMiddleware;
use App\Admin\Request\System\MenuSaveRequest;
use App\Common\Annotation\Permission;
use App\Common\Service\System\MenuService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/api/menu')]
#[Middlewares([AdminAuthMiddleware::class, PermissionMiddleware::class])]
class MenuController extends BaseController
{
    #[Inject]
    protected MenuService $menuService;

    /**
     * 获取用户菜单列表（用于前端动态路由）
     * GET /api/menu/user
     */
    #[GetMapping(path: 'user')]
    public function userMenus(): ResponseInterface
    {
        $menus = $this->menuService->getUserMenus($this->getUserId());
        return $this->response->success($menus);
    }

    /**
     * 获取所有菜单列表（用于管理）
     * GET /api/menu/list
     */
    #[GetMapping(path: 'list')]
    public function list(): ResponseInterface
    {
        $menus = $this->menuService->getList();
        return $this->response->success($menus);
    }

    /**
     * 创建菜单
     * POST /api/menu
     */
    #[PostMapping(path: '')]
    #[Permission(code: 'menu:add')]
    public function create(MenuSaveRequest $form): ResponseInterface
    {
        $menu = $this->menuService->create($form->validatedData());
        return $this->response->success(['id' => $menu->id], '创建成功');
    }

    /**
     * 更新菜单
     * PUT /api/menu/{id}
     */
    #[PutMapping(path: '{id}')]
    #[Permission(code: 'menu:edit')]
    public function update(int $id, MenuSaveRequest $form): ResponseInterface
    {
        $this->menuService->update($id, $form->validatedData());
        return $this->response->success(null, '更新成功');
    }

    /**
     * 删除菜单
     * DELETE /api/menu/{id}
     */
    #[DeleteMapping(path: '{id}')]
    #[Permission(code: 'menu:delete')]
    public function delete(int $id): ResponseInterface
    {
        $this->menuService->delete($id);
        return $this->response->success(null, '删除成功');
    }
}
