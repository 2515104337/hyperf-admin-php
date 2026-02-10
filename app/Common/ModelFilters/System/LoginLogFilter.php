<?php

declare(strict_types=1);

namespace App\Common\ModelFilters\System;

use App\ModelFilters\ModelFilter;

class LoginLogFilter extends ModelFilter
{
    public function userId(mixed $value): void
    {
        $this->where('user_id', (int) $value);
    }

    public function status(mixed $value): void
    {
        $this->where('status', $value);
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
