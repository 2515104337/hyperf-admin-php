<?php

declare(strict_types=1);

namespace App\Common\Model\System;

use App\Common\ModelFilters\System\OperationLogFilter;
use HyperfEloquentFilter\Filterable;

class OperationLog extends Model
{
    use Filterable;

    protected ?string $table = 'operation_logs';

    public bool $timestamps = false;

    protected array $fillable = [
        'user_id',
        'username',
        'method',
        'path',
        'params',
        'status',
        'ip',
        'user_agent',
        'module',
        'description',
        'before_data',
        'after_data',
    ];

    protected array $casts = [
        'user_id' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
    ];

    public function modelFilter(): string
    {
        return OperationLogFilter::class;
    }
}
