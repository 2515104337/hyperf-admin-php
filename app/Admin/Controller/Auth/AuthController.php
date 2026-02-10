<?php

declare(strict_types=1);

namespace App\Admin\Controller\Auth;

use App\Admin\Controller\BaseController;
use App\Admin\Middleware\AdminAuthMiddleware;
use App\Admin\Request\Auth\LoginRequest;
use App\Common\Service\Auth\AuthService;
use App\Common\Service\LoginLogService;
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

    #[Inject]
    protected LoginLogService $loginLogService;

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

    /**
     * 用户登出
     * POST /api/auth/logout
     */
    #[PostMapping(path: 'auth/logout')]
    #[Middleware(AdminAuthMiddleware::class)]
    public function logout(): ResponseInterface
    {
        $userId = $this->getUserId();
        $username = $this->getUsername();
        $ip = $this->request->getServerParams()['remote_addr'] ?? '';
        $userAgent = $this->request->getHeaderLine('User-Agent');

        $this->loginLogService->recordLogout($userId, $username, $ip, $userAgent);

        return $this->response->success(null, '登出成功');
    }
}
