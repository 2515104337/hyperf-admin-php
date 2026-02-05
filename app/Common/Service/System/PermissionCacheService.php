<?php

declare(strict_types=1);

namespace App\Common\Service\System;

use App\Common\Model\System\AdminUser;
use App\Common\Model\System\Role;
use Hyperf\Cache\Cache;
use Hyperf\Di\Annotation\Inject;
use Psr\SimpleCache\InvalidArgumentException;

class PermissionCacheService
{
    // 超级管理员角色代码
    private const SUPER_ADMIN_ROLE = 'R_SUPER';

    // 缓存前缀
    private const CACHE_PREFIX = 'user_permissions:';

    // 缓存过期时间（秒）
    private const CACHE_TTL = 3600;

    #[Inject]
    protected Cache $cache;

    /**
     * 获取用户权限信息（带缓存）
     */
    public function getUserPermissions(int $userId): ?array
    {
        $cacheKey = self::CACHE_PREFIX . $userId;

        // 尝试从缓存获取
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // 缓存未命中，从数据库查询
        $user = AdminUser::with('roles.menus')->where('status', 1)->find($userId);
        if (!$user) {
            return null;
        }

        // 构建权限数据
        $roleCodes = $user->roles->pluck('code')->toArray();
        $isSuperAdmin = in_array(self::SUPER_ADMIN_ROLE, $roleCodes);
        $permissions = $user->buttons;

        $data = [
            'user_id' => $userId,
            'role_codes' => $roleCodes,
            'is_super_admin' => $isSuperAdmin,
            'permissions' => $permissions,
        ];

        // 写入缓存
        $this->cache->set($cacheKey, $data, self::CACHE_TTL);

        return $data;
    }

    /**
     * 清除指定用户的权限缓存
     * @throws InvalidArgumentException
     */
    public function clearUserCache(int $userId): void
    {
        $cacheKey = self::CACHE_PREFIX . $userId;
        $this->cache->delete($cacheKey);
    }

    /**
     * 批量清除用户权限缓存
     * @throws InvalidArgumentException
     */
    public function clearUsersCache(array $userIds): void
    {
        foreach ($userIds as $userId) {
            $this->clearUserCache($userId);
        }
    }

    /**
     * 清除角色下所有用户的权限缓存
     * @throws InvalidArgumentException
     */
    public function clearRoleUsersCache(int $roleId): void
    {
        $role = Role::with('users')->find($roleId);
        if ($role) {
            $userIds = $role->users->pluck('id')->toArray();
            $this->clearUsersCache($userIds);
        }
    }

    /**
     * 清除多个角色下所有用户的权限缓存
     * @throws InvalidArgumentException
     */
    public function clearRolesUsersCache(array $roleIds): void
    {
        foreach ($roleIds as $roleId) {
            $this->clearRoleUsersCache($roleId);
        }
    }
}
