<?php

declare(strict_types=1);

namespace App\Admin\Controller\System;

use App\Admin\Controller\BaseController;
use App\Admin\Middleware\AdminAuthMiddleware;
use App\Admin\Middleware\PermissionMiddleware;
use App\Admin\Request\System\UpdatePasswordRequest;
use App\Admin\Request\System\UserCreateRequest;
use App\Admin\Request\System\UserUpdateRequest;
use App\Common\Annotation\Permission;
use App\Common\Service\System\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/api/user')]
#[Middlewares([AdminAuthMiddleware::class, PermissionMiddleware::class])]
class UserController extends BaseController
{
    #[Inject]
    protected UserService $userService;

    /**
     * 获取用户列表
     * GET /api/user/list
     */
    #[GetMapping(path: 'list')]
    public function list(): ResponseInterface
    {
        $result = $this->userService->getList($this->getParams());
        return $this->response->success($result);
    }

    /**
     * 获取单个用户详情
     * GET /api/user/{id}
     */
    #[GetMapping(path: '{id:\d+}')]
    public function show(int $id): ResponseInterface
    {
        $user = $this->userService->getById($id);
        return $this->response->success($user);
    }

    /**
     * 获取当前用户个人资料
     * GET /api/user/profile
     */
    #[GetMapping(path: 'profile')]
    public function profile(): ResponseInterface
    {
        $profile = $this->userService->getProfile($this->getUserId());
        return $this->response->success($profile);
    }

    /**
     * 更新当前用户个人资料
     * PUT /api/user/profile
     */
    #[PutMapping(path: 'profile')]
    public function updateProfile(): ResponseInterface
    {
        $this->userService->updateProfile($this->getUserId(), $this->getParams());
        return $this->response->success(null, '更新成功');
    }

    /**
     * 修改当前用户密码
     * PUT /api/user/password
     */
    #[PutMapping(path: 'password')]
    public function updatePassword(UpdatePasswordRequest $form): ResponseInterface
    {
        $this->userService->updatePassword($this->getUserId(), $form->validatedData());
        return $this->response->success(null, '密码修改成功');
    }

    /**
     * 创建用户
     * POST /api/user
     */
    #[PostMapping(path: '')]
    #[Permission(code: 'user:add')]
    public function create(UserCreateRequest $form): ResponseInterface
    {
        $user = $this->userService->create($form->validatedData());
        return $this->response->success(['id' => $user->id], '创建成功');
    }

    /**
     * 更新用户
     * PUT /api/user/{id}
     */
    #[PutMapping(path: '{id}')]
    #[Permission(code: 'user:edit')]
    public function update(int $id, UserUpdateRequest $form): ResponseInterface
    {
        $this->userService->update($id, $form->validatedData());
        return $this->response->success(null, '更新成功');
    }

    /**
     * 删除用户
     * DELETE /api/user/{id}
     */
    #[DeleteMapping(path: '{id}')]
    #[Permission(code: 'user:delete')]
    public function delete(int $id): ResponseInterface
    {
        $this->userService->delete($id);
        return $this->response->success(null, '删除成功');
    }
}
