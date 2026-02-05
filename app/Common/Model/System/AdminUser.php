<?php

declare(strict_types=1);

namespace App\Common\Model\System;

use App\Common\Enum\GenderEnum;
use App\Common\ModelFilters\System\AdminUserFilter;
use Hyperf\Database\Model\Relations\BelongsToMany;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\Database\Model\Builder;
use HyperfEloquentFilter\Filterable;

/**
 * Class AdminUser
 *
 * @method static Builder filter(array $input = [], string|null $filter = null)
 */
class AdminUser extends Model
{
    use SoftDeletes;
    use Filterable;

    protected ?string $table = 'admin_users';

    /**
     * 指定模型过滤器类
     */
    public function modelFilter(): string
    {
        return AdminUserFilter::class;
    }

    protected array $fillable = [
        'username',
        'password',
        'nickname',
        'real_name',
        'avatar',
        'email',
        'phone',
        'address',
        'description',
        'gender',
        'status',
        'created_by',
        'updated_by',
    ];

    protected array $hidden = [
        'password',
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'status' => 'integer',
        'gender' => GenderEnum::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取用户角色
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'admin_user_roles',
            'user_id',
            'role_id'
        );
    }

    /**
     * 获取用户权限按钮
     */
    public function getButtonsAttribute(): array
    {
        $buttons = [];
        foreach ($this->roles as $role) {
            foreach ($role->menus as $menu) {
                if ($menu->type === 3 && $menu->permission) {
                    $buttons[] = $menu->permission;
                }
            }
        }
        return array_unique($buttons);
    }

    /**
     * 获取用户角色代码
     */
    public function getRoleCodesAttribute(): array
    {
        return $this->roles->pluck('code')->toArray();
    }
}
