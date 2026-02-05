<?php

declare(strict_types=1);

namespace App\Admin\Controller\Auth;

use App\Admin\Controller\BaseController;
use App\Admin\Middleware\AdminAuthMiddleware;
use App\Admin\Request\Auth\LoginRequest;
use App\Common\Service\Auth\AuthService;
use Exception;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/api')]
class AuthController extends BaseController
{
    #[Inject]
    protected AuthService $authService;

    /**
     * 用户登录
     * POST /api/auth/login
     * @throws Exception
     */
    #[PostMapping(path: 'auth/login')]
    public function login(LoginRequest $form): ResponseInterface
    {
        $data = $form->validatedData();

        $result = $this->authService->login($data['username'], $data['password']);
        return $this->response->success($result);
    }

    /**
     * 获取当前用户信息
     * GET /api/user/info
     */
    #[GetMapping(path: 'user/info')]
    #[Middleware(AdminAuthMiddleware::class)]
    public function userInfo(): ResponseInterface
    {
        $userInfo = $this->authService->getUserInfo($this->getUserId());
        return $this->response->success($userInfo);
    }
}
