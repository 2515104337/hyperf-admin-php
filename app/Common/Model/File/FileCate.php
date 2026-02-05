<?php

declare(strict_types=1);

namespace App\Common\Model\File;

use App\Common\Model\System\Model;
use Hyperf\Database\Model\SoftDeletes;

/**
 * 文件分类模型
 *
 * @property int $id
 * @property int $pid
 * @property int $type
 * @property string $name
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 */
class FileCate extends Model
{
    use SoftDeletes;

    protected ?string $table = 'file_cate';

    protected array $fillable = [
        'pid',
        'type',
        'name',
    ];

    protected array $hidden = [
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'pid' => 'integer',
        'type' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取子分类
     */
    public function children()
    {
        return $this->hasMany(self::class, 'pid', 'id');
    }

    /**
     * 获取父分类
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'pid', 'id');
    }

    /**
     * 获取分类下的文件
     */
    public function files()
    {
        return $this->hasMany(File::class, 'cid', 'id');
    }
}
