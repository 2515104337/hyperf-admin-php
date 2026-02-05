<?php

declare(strict_types=1);

namespace App\Common\ModelFilters\System;

use App\ModelFilters\ModelFilter;

/**
 * Role 模型过滤器
 */
class RoleFilter extends ModelFilter
{
    /**
     * 按角色名称搜索（模糊匹配）
     */
    public function roleName(string $value): void
    {
        $this->whereLike('name', "%{$value}%");
    }

    /**
     * 按角色代码搜索（模糊匹配）
     */
    public function roleCode(string $value): void
    {
        $this->whereLike('code', "%{$value}%");
    }

    /**
     * 按角色描述搜索（模糊匹配）
     */
    public function description(string $value): void
    {
        $this->whereLike('description', "%{$value}%");
    }


}
