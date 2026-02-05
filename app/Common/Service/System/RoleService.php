<?php

declare(strict_types=1);

namespace App\Common\Service\System;

use App\Common\Exception\BusinessException;
use App\Common\Model\System\Role;
use App\Common\Trait\PaginateTrait;
use Hyperf\Di\Annotation\Inject;

class RoleService
{
    use PaginateTrait;

    #[Inject]
    protected PermissionCacheService $permissionCacheService;

    /**
     * 获取角色列表（分页）
     */
    public function getList(array $params): array
    {
        $query = Role::filter($params)->orderBy('sort');

        return $this->paginate(
            $query,
            $params,
            fn ($role) => $this->formatRole($role)
        );
    }

    /**
     * 获取单个角色详情
     */
    public function getById(int $id): array
    {
        $role = Role::find($id);

        if (!$role) {
            throw new BusinessException('角色不存在');
        }

        return $this->formatRole($role);
    }

    /**
     * 获取所有角色（不分页，用于下拉选择）
     */
    public function getAll(): array
    {
        $roles = Role::where('enabled', true)
            ->orderBy('sort')
            ->get();

        return $roles->map(fn ($role) => $this->formatRole($role))->toArray();
    }

    /**
     * 创建角色
     */
    public function create(array $data): Role
    {
        if (Role::where('code', $data['code'])->exists()) {
            throw new BusinessException('角色代码已存在');
        }

        $role = Role::create([
            'name' => $data['name'],
            'code' => $data['code'],
            'description' => $data['description'] ?? '',
            'enabled' => $data['enabled'] ?? true,
            'sort' => $data['sort'] ?? 0,
        ]);

        // 分配菜单权限
        if (!empty($data['menuIds'])) {
            $role->menus()->sync($data['menuIds']);
        }

        return $role;
    }

    /**
     * 更新角色
     */
    public function update(int $id, array $data): Role
    {
        $role = Role::findOrFail($id);

        $updateData = [];
        if (isset($data['name'])) $updateData['name'] = $data['name'];
        if (isset($data['description'])) $updateData['description'] = $data['description'];
        if (isset($data['enabled'])) $updateData['enabled'] = $data['enabled'];
        if (isset($data['sort'])) $updateData['sort'] = $data['sort'];
        // code 不允许修改

        if (!empty($updateData)) {
            $role->update($updateData);
        }

        // 更新菜单权限
        if (isset($data['menuIds'])) {
            $role->menus()->sync($data['menuIds']);
            // 菜单权限变化，清除该角色下所有用户的权限缓存
            $this->permissionCacheService->clearRoleUsersCache($id);
        }

        return $role->fresh();
    }

    /**
     * 删除角色
     */
    public function delete(int $id): void
    {
        $role = Role::findOrFail($id);
        // 删除角色前，清除该角色下所有用户的权限缓存
        $this->permissionCacheService->clearRoleUsersCache($id);
        $role->users()->detach();
        $role->menus()->detach();
        $role->delete();
    }

    /**
     * 获取角色菜单权限
     */
    public function getMenuIds(int $id): array
    {
        $role = Role::findOrFail($id);
        return $role->menus()->pluck('menus.id')->toArray();
    }

    /**
     * 更新角色菜单权限
     */
    public function updateMenus(int $id, array $menuIds): void
    {
        $role = Role::findOrFail($id);
        $role->menus()->sync($menuIds);
        // 菜单权限变化，清除该角色下所有用户的权限缓存
        $this->permissionCacheService->clearRoleUsersCache($id);
    }

    /**
     * 格式化角色数据（直接返回数据库字段）
     */
    protected function formatRole(Role $role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'code' => $role->code,
            'description' => $role->description,
            'enabled' => $role->enabled,
            'created_at' => $role->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
