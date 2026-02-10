<?php

declare(strict_types=1);

namespace App\Job;

use App\Common\Model\System\OperationLog;
use Hyperf\AsyncQueue\Job;

class OperationLogJob extends Job
{
    public int $maxAttempts = 3;

    public function __construct(protected array $data)
    {
    }

    public function handle(): void
    {
        OperationLog::create($this->data);
    }
}
