<?php

declare(strict_types=1);

namespace App\Common\Model\System;

use App\Common\ModelFilters\System\LoginLogFilter;
use HyperfEloquentFilter\Filterable;

class LoginLog extends Model
{
    use Filterable;

    protected ?string $table = 'login_logs';

    public bool $timestamps = false;

    protected array $fillable = [
        'user_id',
        'username',
        'action',
        'status',
        'failure_reason',
        'ip',
        'user_agent',
        'browser',
        'os',
    ];

    protected array $casts = [
        'user_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function modelFilter(): string
    {
        return LoginLogFilter::class;
    }
}
