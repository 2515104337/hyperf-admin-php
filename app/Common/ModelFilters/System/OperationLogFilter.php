<?php

declare(strict_types=1);

namespace App\Common\ModelFilters\System;

use App\ModelFilters\ModelFilter;

class OperationLogFilter extends ModelFilter
{
    public function userId(mixed $value): void
    {
        $this->where('user_id', (int) $value);
    }

    public function module(mixed $value): void
    {
        $this->where('module', $value);
    }

    public function startDate(string $value): void
    {
        $this->where('created_at', '>=', $value);
    }

    public function endDate(string $value): void
    {
        $this->where('created_at', '<=', $value);
    }
}
