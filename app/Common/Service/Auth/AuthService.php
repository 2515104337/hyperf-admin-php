<?php

declare(strict_types=1);

namespace App\Common\Service\Auth;

use App\Common\Exception\BusinessException;
use App\Common\Model\System\AdminUser;
use App\Common\Service\LoginLogService;
use Hyperf\Context\ApplicationContext;
use Phper666\JWTAuth\JWT;

class AuthService
{
    public function __construct(
        protected JWT $jwt
    ) {}

    private function getLoginLogService(): LoginLogService
    {
        return ApplicationContext::getContainer()->get(LoginLogService::class);
    }

    /**
     * 用户登录
     * @throws \Exception
     */
    public function login(string $username, string $password): array
    {
        $user = AdminUser::where('username', $username)->first();

        $request = ApplicationContext::getContainer()->get(\Hyperf\HttpServer\Contract\RequestInterface::class);
        $ip = $this->getClientIp($request);
        $userAgent = $request->getHeaderLine('User-Agent');

        if (!$user) {
            $this->getLoginLogService()->recordLogin([
                'user_id' => null,
                'username' => $username,
                'status' => 'failed',
                'failure_reason' => '用户不存在',
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);
            throw new BusinessException('用户不存在');
        }

        if ($user->status !== 1) {
            $this->getLoginLogService()->recordLogin([
                'user_id' => $user->id,
                'username' => $username,
                'status' => 'failed',
                'failure_reason' => '用户已被禁用',
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);
            throw new BusinessException('用户已被禁用');
        }

        if (!password_verify($password, $user->password)) {
            $this->getLoginLogService()->recordLogin([
                'user_id' => $user->id,
                'username' => $username,
                'status' => 'failed',
                'failure_reason' => '密码错误',
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);
            throw new BusinessException('密码错误');
        }

        // 生成 token
        $token = $this->jwt->getToken('default', [
            'uid' => $user->id,
            'username' => $user->username,
        ]);

        try {
            $this->getLoginLogService()->recordLogin([
                'user_id' => $user->id,
                'username' => $username,
                'status' => 'success',
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);
        } catch (\Throwable) {
        }

        return [
            'token' => $token->toString(),
            'refreshToken' => $token->toString(),
        ];
    }

    /**
     * 获取当前用户信息
     */
    public function getUserInfo(int $userId): array
    {
        $user = AdminUser::with('roles.menus')->find($userId);

        if (!$user) {
            throw new BusinessException('用户不存在');
        }

        return [
            'userId' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'roles' => $user->role_codes,
            'buttons' => $user->buttons,
        ];
    }

    /**
     * 从 Token 获取用户 ID
     */
    public function getUserIdFromToken(string $token): int
    {
        $claims = $this->jwt->getClaimsByToken($token);
        return (int) ($claims['uid'] ?? 0);
    }

    /**
     * 验证 Token
     */
    public function validateToken(string $token): bool
    {
        try {
            return $this->jwt->verifyToken($token);
        } catch (\Throwable) {
            return false;
        }
    }

    private function getClientIp($request = null): string
    {
        if (!$request) {
            $request = ApplicationContext::getContainer()->get(\Hyperf\HttpServer\Contract\RequestInterface::class);
        }
        $serverParams = $request->getServerParams();

        if (isset($serverParams['http_x_forwarded_for'])) {
            return explode(',', $serverParams['http_x_forwarded_for'])[0];
        }

        if (isset($serverParams['http_x_real_ip'])) {
            return $serverParams['http_x_real_ip'];
        }

        return $serverParams['remote_addr'] ?? '';
    }
}
