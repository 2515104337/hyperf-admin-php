<?php

declare(strict_types=1);

namespace App\Common\ModelFilters\System;

use App\Common\Enum\GenderEnum;
use App\ModelFilters\ModelFilter;

/**
 * AdminUser 模型过滤器
 */
class AdminUserFilter extends ModelFilter
{
    /**
     * 按用户名搜索（模糊匹配）
     */
    public function userName(string $value): void
    {
        $this->whereLike('username', "%{$value}%");
    }

    /**
     * 按邮箱搜索（模糊匹配）
     */
    public function userEmail(string $value): void
    {
        $this->whereLike('email', "%{$value}%");
    }

    /**
     * 按手机号搜索（模糊匹配）
     */
    public function userPhone(string $value): void
    {
        $this->whereLike('phone', "%{$value}%");
    }

    /**
     * 按性别筛选
     */
    public function userGender(string $value): void
    {
        $this->where('gender', GenderEnum::fromFrontend($value)->value);
    }

    /**
     * 按状态筛选
     */
    public function status(mixed $value): void
    {
        $this->where('status', $value);
    }
}
