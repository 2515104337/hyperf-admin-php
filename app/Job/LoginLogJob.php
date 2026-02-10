<?php

declare(strict_types=1);

namespace App\Job;

use App\Common\Model\System\LoginLog;
use Hyperf\AsyncQueue\Job;

class LoginLogJob extends Job
{
    public int $maxAttempts = 3;

    public function __construct(protected array $data)
    {
    }

    public function handle(): void
    {
        LoginLog::create($this->data);
    }
}
