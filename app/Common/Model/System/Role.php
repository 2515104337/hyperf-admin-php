<?php

declare(strict_types=1);

namespace App\Common\Model\System;

use App\Common\ModelFilters\System\RoleFilter;
use Hyperf\Database\Model\SoftDeletes;
use HyperfEloquentFilter\Filterable;

class Role extends Model
{
    use SoftDeletes;
    use Filterable;

    protected ?string $table = 'roles';

    /**
     * 指定模型过滤器类
     */
    public function modelFilter(): string
    {
        return RoleFilter::class;
    }

    protected array $fillable = [
        'name',
        'code',
        'description',
        'enabled',
        'sort',
    ];

    protected array $casts = [
        'id' => 'integer',
        'enabled' => 'boolean',
        'sort' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected array $hidden = [
        'deleted_at',
    ];

    /**
     * 获取角色用户
     */
    public function users()
    {
        return $this->belongsToMany(
            AdminUser::class,
            'admin_user_roles',
            'role_id',
            'user_id'
        );
    }

    /**
     * 获取角色菜单
     */
    public function menus()
    {
        return $this->belongsToMany(
            Menu::class,
            'role_menus',
            'role_id',
            'menu_id'
        );
    }
}
