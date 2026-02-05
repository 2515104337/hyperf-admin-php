<?php

declare(strict_types=1);

namespace App\Common\Service\System;

use App\Common\Model\System\AdminUser;
use App\Common\Model\System\Menu;

class MenuService
{
    /**
     * 获取用户菜单树（用于前端路由）
     */
    public function getUserMenus(int $userId): array
    {
        $user = AdminUser::with('roles.menus')->find($userId);

        if (!$user) {
            return [];
        }

        // 获取用户所有菜单ID
        $menuIds = [];
        foreach ($user->roles as $role) {
            $menuIds = array_merge($menuIds, $role->menus->pluck('id')->toArray());
        }
        $menuIds = array_unique($menuIds);

        // 获取所有菜单（目录和菜单，不包含按钮）
        $menus = Menu::whereIn('id', $menuIds)
            ->whereIn('type', [1, 2])
            ->where('enabled', true)
            ->orderBy('sort')
            ->get();

        return $this->buildMenuTree($menus->toArray());
    }

    /**
     * 获取所有菜单列表
     */
    public function getList(): array
    {
        // 获取所有菜单（包含按钮 type=3）
        $menus = Menu::where('enabled', true)
            ->orderBy('sort')
            ->get()
            ->toArray();

        return $this->buildMenuTreeWithButtons($menus);
    }

    /**
     * 创建菜单
     */
    public function create(array $data): Menu
    {
        // 根据菜单类型设置 type
        $menuType = $data['menuType'] ?? 'menu';
        $type = match ($menuType) {
            'button' => 3,  // 按钮
            default => 2,   // 菜单
        };

        // 如果有子菜单，则为目录类型
        if ($type === 2 && empty($data['component'])) {
            $type = 1; // 目录
        }

        return Menu::create([
            'parent_id' => $data['parentId'] ?? 0,
            'name' => $menuType === 'button' ? ($data['authLabel'] ?? '') : ($data['label'] ?? $data['name'] ?? ''),
            'path' => $data['path'] ?? '',
            'component' => $data['component'] ?? '',
            'redirect' => $data['redirect'] ?? '',
            'title' => $menuType === 'button' ? ($data['authName'] ?? '') : ($data['name'] ?? $data['title'] ?? ''),
            'icon' => $data['icon'] ?? '',
            'is_hide' => $data['isHide'] ?? false,
            'is_hide_tab' => $data['isHideTab'] ?? false,
            'link' => $data['link'] ?? '',
            'is_iframe' => $data['isIframe'] ?? false,
            'keep_alive' => $data['keepAlive'] ?? false,
            'is_affix' => $data['fixedTab'] ?? $data['isAffix'] ?? false,
            'show_badge' => $data['showBadge'] ?? false,
            'show_text_badge' => $data['showTextBadge'] ?? '',
            'is_full_page' => $data['isFullPage'] ?? false,
            'active_path' => $data['activePath'] ?? '',
            'type' => $type,
            'permission' => $menuType === 'button' ? ($data['authLabel'] ?? '') : ($data['permission'] ?? ''),
            'sort' => $menuType === 'button' ? ($data['authSort'] ?? 0) : ($data['sort'] ?? 0),
            'enabled' => $data['isEnable'] ?? $data['enabled'] ?? true,
        ]);
    }

    /**
     * 更新菜单
     */
    public function update(int $id, array $data): Menu
    {
        $menu = Menu::findOrFail($id);

        // 根据菜单类型处理字段
        $menuType = $data['menuType'] ?? ($menu->type === 3 ? 'button' : 'menu');

        $updateData = [];

        if ($menuType === 'button') {
            // 按钮类型
            if (isset($data['authName'])) $updateData['title'] = $data['authName'];
            if (isset($data['authLabel'])) {
                $updateData['name'] = $data['authLabel'];
                $updateData['permission'] = $data['authLabel'];
            }
            if (isset($data['authSort'])) $updateData['sort'] = $data['authSort'];
        } else {
            // 菜单类型
            if (isset($data['name'])) $updateData['title'] = $data['name'];
            if (isset($data['label'])) $updateData['name'] = $data['label'];
            if (isset($data['path'])) $updateData['path'] = $data['path'];
            if (isset($data['component'])) $updateData['component'] = $data['component'];
            if (isset($data['redirect'])) $updateData['redirect'] = $data['redirect'];
            if (isset($data['icon'])) $updateData['icon'] = $data['icon'];
            if (isset($data['isHide'])) $updateData['is_hide'] = $data['isHide'];
            if (isset($data['isHideTab'])) $updateData['is_hide_tab'] = $data['isHideTab'];
            if (isset($data['link'])) $updateData['link'] = $data['link'];
            if (isset($data['isIframe'])) $updateData['is_iframe'] = $data['isIframe'];
            if (isset($data['keepAlive'])) $updateData['keep_alive'] = $data['keepAlive'];
            if (isset($data['fixedTab'])) $updateData['is_affix'] = $data['fixedTab'];
            if (isset($data['showBadge'])) $updateData['show_badge'] = $data['showBadge'];
            if (isset($data['showTextBadge'])) $updateData['show_text_badge'] = $data['showTextBadge'];
            if (isset($data['isFullPage'])) $updateData['is_full_page'] = $data['isFullPage'];
            if (isset($data['activePath'])) $updateData['active_path'] = $data['activePath'];
            if (isset($data['sort'])) $updateData['sort'] = $data['sort'];
            if (isset($data['permission'])) $updateData['permission'] = $data['permission'];
        }

        // 通用字段
        if (isset($data['parentId'])) $updateData['parent_id'] = $data['parentId'];
        if (isset($data['isEnable'])) $updateData['enabled'] = $data['isEnable'];
        if (isset($data['enabled'])) $updateData['enabled'] = $data['enabled'];

        $menu->update($updateData);
        return $menu->fresh();
    }

    /**
     * 删除菜单
     */
    public function delete(int $id): void
    {
        $menu = Menu::findOrFail($id);

        // 递归删除所有子菜单
        $this->deleteChildrenRecursively($id);

        // 删除角色关联
        $menu->roles()->detach();

        $menu->delete();
    }

    /**
     * 递归删除子菜单
     */
    protected function deleteChildrenRecursively(int $parentId): void
    {
        $children = Menu::where('parent_id', $parentId)->get();

        foreach ($children as $child) {
            // 递归删除更深层的子菜单
            $this->deleteChildrenRecursively($child->id);
            // 删除角色关联
            $child->roles()->detach();
            // 删除子菜单
            $child->delete();
        }
    }

    /**
     * 构建菜单树
     */
    protected function buildMenuTree(array $menus, int $parentId = 0): array
    {
        $tree = [];
        foreach ($menus as $menu) {
            if ((int) $menu['parent_id'] === $parentId) {
                $children = $this->buildMenuTree($menus, (int) $menu['id']);
                $item = $this->formatMenu($menu);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 构建菜单树（包含按钮权限）
     */
    protected function buildMenuTreeWithButtons(array $menus, int $parentId = 0): array
    {
        $tree = [];
        foreach ($menus as $menu) {
            // 跳过按钮类型（type=3），按钮会被添加到父菜单的 authList 中
            if ((int) $menu['type'] === 3) {
                continue;
            }

            if ((int) $menu['parent_id'] === $parentId) {
                // 获取该菜单下的按钮权限
                $authList = $this->getMenuButtons($menus, (int) $menu['id']);

                // 递归获取子菜单
                $children = $this->buildMenuTreeWithButtons($menus, (int) $menu['id']);

                $item = $this->formatMenuWithAuth($menu, $authList);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 获取菜单下的按钮权限
     */
    protected function getMenuButtons(array $menus, int $parentId): array
    {
        $buttons = [];
        foreach ($menus as $menu) {
            if ((int) $menu['parent_id'] === $parentId && (int) $menu['type'] === 3) {
                $buttons[] = [
                    'id' => $menu['id'],
                    'title' => $menu['title'],
                    'authMark' => $menu['permission'],
                    'sort' => (int) ($menu['sort'] ?? 0),
                ];
            }
        }
        return $buttons;
    }

    /**
     * 格式化菜单数据（包含按钮权限）
     */
    protected function formatMenuWithAuth(array $menu, array $authList = []): array
    {
        $result = [
            'id' => $menu['id'],
            'path' => $menu['path'],
            'name' => $menu['name'],
            'meta' => [
                'title' => $menu['title'],
                'icon' => $menu['icon'],
                'isHide' => (bool) $menu['is_hide'],
                'isHideTab' => (bool) $menu['is_hide_tab'],
                'link' => $menu['link'] ?: null,
                'isIframe' => (bool) $menu['is_iframe'],
                'keepAlive' => (bool) $menu['keep_alive'],
                'fixedTab' => (bool) $menu['is_affix'],
                'showBadge' => (bool) ($menu['show_badge'] ?? false),
                'showTextBadge' => $menu['show_text_badge'] ?? '',
                'isFullPage' => (bool) ($menu['is_full_page'] ?? false),
                'activePath' => $menu['active_path'] ?? '',
                'isEnable' => (bool) ($menu['enabled'] ?? true),
                'sort' => (int) ($menu['sort'] ?? 0),
            ],
        ];

        // 添加按钮权限列表
        if (!empty($authList)) {
            $result['meta']['authList'] = $authList;
        }

        if (!empty($menu['component'])) {
            $result['component'] = $menu['component'];
        }

        if (!empty($menu['redirect'])) {
            $result['redirect'] = $menu['redirect'];
        }

        return $result;
    }

    /**
     * 格式化菜单数据（匹配前端 AppRouteRecord）
     */
    protected function formatMenu(array $menu): array
    {
        $result = [
            'id' => $menu['id'],
            'path' => $menu['path'],
            'name' => $menu['name'],
            'meta' => [
                'title' => $menu['title'],
                'icon' => $menu['icon'],
                'isHide' => (bool) $menu['is_hide'],
                'isHideTab' => (bool) $menu['is_hide_tab'],
                'link' => $menu['link'] ?: null,
                'isIframe' => (bool) $menu['is_iframe'],
                'keepAlive' => (bool) $menu['keep_alive'],
                'fixedTab' => (bool) $menu['is_affix'],
                'showBadge' => (bool) ($menu['show_badge'] ?? false),
                'showTextBadge' => $menu['show_text_badge'] ?? '',
                'isFullPage' => (bool) ($menu['is_full_page'] ?? false),
                'activePath' => $menu['active_path'] ?? '',
                'isEnable' => (bool) ($menu['enabled'] ?? true),
                'sort' => (int) ($menu['sort'] ?? 0),
            ],
        ];

        if (!empty($menu['component'])) {
            $result['component'] = $menu['component'];
        }

        if (!empty($menu['redirect'])) {
            $result['redirect'] = $menu['redirect'];
        }

        return $result;
    }
}
