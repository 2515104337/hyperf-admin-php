<?php

declare(strict_types=1);

namespace App\Common\Service\Auth;

use App\Common\Exception\BusinessException;
use App\Common\Model\System\AdminUser;
use Phper666\JWTAuth\JWT;

class AuthService
{
    public function __construct(
        protected JWT $jwt
    ) {}

    /**
     * 用户登录
     * @throws \Exception
     */
    public function login(string $username, string $password): array
    {
        $user = AdminUser::where('username', $username)->first();

        if (!$user) {
            throw new BusinessException('用户不存在');
        }

        if ($user->status !== 1) {
            throw new BusinessException('用户已被禁用');
        }

        if (!password_verify($password, $user->password)) {
            throw new BusinessException('密码错误');
        }

        // 生成 token
        $token = $this->jwt->getToken('default', [
            'uid' => $user->id,
            'username' => $user->username,
        ]);

        return [
            'token' => $token->toString(),
            'refreshToken' => $token->toString(), // 暂时使用同一个 token
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
}
