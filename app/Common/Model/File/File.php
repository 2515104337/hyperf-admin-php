<?php

declare(strict_types=1);

namespace App\Common\Model\File;

use App\Common\Enum\FileEnum;
use App\Common\Model\System\Model;
use App\Common\ModelFilters\File\FileFilter;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\SoftDeletes;
use HyperfEloquentFilter\Filterable;

/**
 * 文件模型
 *
 * @property int $id
 * @property int $cid
 * @property int $type
 * @property string $name
 * @property string $uri
 * @property int $source
 * @property int $source_id
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 *
 * @method static Builder filter(array $input = [], string|null $filter = null)
 */
class File extends Model
{
    use SoftDeletes;
    use Filterable;

    protected ?string $table = 'file';

    protected array $fillable = [
        'cid',
        'type',
        'name',
        'uri',
        'source',
        'source_id',
    ];

    protected array $hidden = [
        'deleted_at',
    ];

    protected array $casts = [
        'id' => 'integer',
        'cid' => 'integer',
        'type' => 'integer',
        'source' => 'integer',
        'source_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 指定模型过滤器类
     */
    public function modelFilter(): string
    {
        return FileFilter::class;
    }

    /**
     * 获取分类
     */
    public function cate()
    {
        return $this->belongsTo(FileCate::class, 'cid', 'id');
    }

    /**
     * 获取类型名称
     */
    public function getTypeNameAttribute(): string
    {
        return FileEnum::getTypeName($this->type);
    }

    /**
     * 获取来源名称
     */
    public function getSourceNameAttribute(): string
    {
        return FileEnum::getSourceName($this->source);
    }
}
