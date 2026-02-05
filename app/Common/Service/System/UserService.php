<?php

declare(strict_types=1);

namespace App\Common\Service\System;

use App\Common\Exception\BusinessException;
use App\Common\Model\System\AdminUser;
use App\Common\Model\System\Role;
use App\Common\Trait\PaginateTrait;
use Hyperf\Di\Annotation\Inject;

class UserService
{
    use PaginateTrait;

    #[Inject]
    protected PermissionCacheService $permissionCacheService;

    /**
     * 获取用户列表（分页）
     */
    public function getList(array $params): array
    {
        $query = AdminUser::filter($params)->with('roles');

        return $this->paginate(
            $query->orderByDesc('id'),
            $params,
            fn ($user) => $this->formatUser($user)
        );
    }

    /**
     * 获取单个用户详情
     */
    public function getById(int $id): array
    {
        $user = AdminUser::with('roles')->find($id);

        if (!$user) {
            throw new BusinessException('用户不存在');
        }

        return $this->formatUser($user);
    }

    /**
     * 创建用户
     */
    public function create(array $data): AdminUser
    {
        // 检查用户名是否存在（排除已软删除的记录）
        if (AdminUser::where('username', $data['username'])->exists()) {
            throw new BusinessException('用户名已存在');
        }

        // 检查是否有被软删除的同名用户
        $trashedUser = AdminUser::onlyTrashed()->where('username', $data['username'])->first();
        if ($trashedUser) {
            // 恢复被删除的用户并更新信息
            $trashedUser->restore();
            $trashedUser->update([
                'password' => password_hash($data['password'] ?? '', PASSWORD_DEFAULT),
                'nickname' => $data['nickname'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'gender' => $data['gender'] ?? 'unknown',
                'status' => $data['status'] ?? 1,
                'avatar' => $data['avatar'] ?? '',
            ]);

            // 分配角色
            if (!empty($data['roles'])) {
                $roleIds = Role::whereIn('code', $data['roles'])->pluck('id')->toArray();
                $trashedUser->roles()->sync($roleIds);
            }

            return $trashedUser;
        }

        // 密码必填校验
        if (empty($data['password'])) {
            throw new BusinessException('密码不能为空');
        }

        $user = AdminUser::create([
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'nickname' => $data['nickname'] ?? '',
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'gender' => $data['gender'] ?? 'unknown',
            'status' => $data['status'] ?? 1,
            'avatar' => $data['avatar'] ?? '',
        ]);

        // 分配角色（根据角色代码查询ID）
        if (!empty($data['roles'])) {
            $roleIds = Role::whereIn('code', $data['roles'])->pluck('id')->toArray();
            $user->roles()->sync($roleIds);
        }

        return $user;
    }

    /**
     * 更新用户
     */
    public function update(int $id, array $data): AdminUser
    {
        $user = AdminUser::findOrFail($id);

        $updateData = [];
        if (isset($data['username'])) $updateData['username'] = $data['username'];
        if (isset($data['nickname'])) $updateData['nickname'] = $data['nickname'];
        if (isset($data['email'])) $updateData['email'] = $data['email'];
        if (isset($data['phone'])) $updateData['phone'] = $data['phone'];
        if (isset($data['gender'])) $updateData['gender'] = $data['gender'];
        if (isset($data['status'])) $updateData['status'] = $data['status'];
        if (isset($data['avatar'])) $updateData['avatar'] = $data['avatar'];
        if (!empty($data['password'])) {
            $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        // 更新角色（根据角色代码查询ID）
        if (isset($data['roles'])) {
            $roleIds = Role::whereIn('code', $data['roles'])->pluck('id')->toArray();
            $user->roles()->sync($roleIds);
            // 角色变化，清除用户权限缓存
            $this->permissionCacheService->clearUserCache($id);
        }

        return $user->fresh();
    }

    /**
     * 删除用户
     */
    public function delete(int $id): void
    {
        $user = AdminUser::findOrFail($id);
        $user->roles()->detach();
        $user->delete();
        // 清除用户权限缓存
        $this->permissionCacheService->clearUserCache($id);
    }

    /**
     * 获取用户个人资料
     */
    public function getProfile(int $userId): array
    {
        $user = AdminUser::find($userId);

        if (!$user) {
            throw new BusinessException('用户不存在');
        }

        return [
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname ?? '',
            'real_name' => $user->real_name ?? '',
            'email' => $user->email ?? '',
            'phone' => $user->phone ?? '',
            'gender' => $user->gender?->value ?? 'unknown',
            'address' => $user->address ?? '',
            'description' => $user->description ?? '',
            'avatar' => $user->avatar ?? '',
        ];
    }

    /**
     * 更新用户个人资料
     */
    public function updateProfile(int $userId, array $data): void
    {
        $user = AdminUser::findOrFail($userId);

        $updateData = [];
        if (isset($data['nickname'])) $updateData['nickname'] = $data['nickname'];
        if (isset($data['real_name'])) $updateData['real_name'] = $data['real_name'];
        if (isset($data['email'])) $updateData['email'] = $data['email'];
        if (isset($data['phone'])) $updateData['phone'] = $data['phone'];
        if (isset($data['gender'])) $updateData['gender'] = $data['gender'];
        if (isset($data['address'])) $updateData['address'] = $data['address'];
        if (isset($data['description'])) $updateData['description'] = $data['description'];
        if (isset($data['avatar'])) $updateData['avatar'] = $data['avatar'];

        if (!empty($updateData)) {
            $user->update($updateData);
        }
    }

    /**
     * 修改用户密码
     * @throws BusinessException
     */
    public function updatePassword(int $userId, array $data): void
    {
        $user = AdminUser::findOrFail($userId);

        // 验证当前密码
        if (empty($data['password'])) {
            throw new BusinessException('请输入当前密码');
        }
        if (!password_verify($data['password'], $user->password)) {
            throw new BusinessException('当前密码错误');
        }

        // 验证新密码
        if (empty($data['newPassword'])) {
            throw new BusinessException('请输入新密码');
        }
        if (strlen($data['newPassword']) < 6) {
            throw new BusinessException('新密码长度不能少于6位');
        }
        if ($data['newPassword'] !== ($data['confirmPassword'] ?? '')) {
            throw new BusinessException('两次输入的密码不一致');
        }

        $user->update([
            'password' => password_hash($data['newPassword'], PASSWORD_DEFAULT),
        ]);
    }

    /**
     * 格式化用户数据（直接返回数据库字段）
     */
    protected function formatUser(AdminUser $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'nickname' => $user->nickname,
            'avatar' => $user->avatar,
            'email' => $user->email,
            'phone' => $user->phone,
            'gender' => $user->gender?->value ?? 'unknown',
            'status' => (string) $user->status,
            'roles' => $user->roles->pluck('code')->toArray(),
            'created_by' => $user->created_by,
            'created_at' => $user->created_at?->format('Y-m-d H:i:s'),
            'updated_by' => $user->updated_by,
            'updated_at' => $user->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
