<?php

declare(strict_types=1);

namespace App\Common\Model\System;

use Hyperf\Database\Model\Relations\BelongsTo;
use Hyperf\Database\Model\Relations\BelongsToMany;
use Hyperf\Database\Model\Relations\HasMany;
use Hyperf\Database\Model\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;

    protected ?string $table = 'menus';

    protected array $fillable = [
        'parent_id',
        'name',
        'path',
        'component',
        'redirect',
        'title',
        'icon',
        'is_hide',
        'is_hide_tab',
        'link',
        'is_iframe',
        'keep_alive',
        'is_affix',
        'show_badge',
        'show_text_badge',
        'is_full_page',
        'active_path',
        'type',
        'permission',
        'sort',
        'enabled',
    ];

    protected array $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'is_hide' => 'boolean',
        'is_hide_tab' => 'boolean',
        'is_iframe' => 'boolean',
        'keep_alive' => 'boolean',
        'is_affix' => 'boolean',
        'show_badge' => 'boolean',
        'is_full_page' => 'boolean',
        'type' => 'integer',
        'sort' => 'integer',
        'enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected array $hidden = [
        'deleted_at',
    ];

    /**
     * 获取子菜单
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id')
            ->where('enabled', true)
            ->orderBy('sort');
    }

    /**
     * 获取父菜单
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    /**
     * 获取角色
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_menus',
            'menu_id',
            'role_id'
        );
    }
}
