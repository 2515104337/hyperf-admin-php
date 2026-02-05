<?php

declare(strict_types=1);

namespace App\Common\Model\System;

use Hyperf\DbConnection\Model\Model as BaseModel;

abstract class Model extends BaseModel
{
    protected ?string $dateFormat = 'Y-m-d H:i:s';

    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
}
