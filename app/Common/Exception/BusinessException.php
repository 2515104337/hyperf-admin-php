<?php

declare(strict_types=1);

namespace App\Common\Exception;

use App\Common\Enum\ErrorCode;
use Hyperf\Server\Exception\ServerException;

class BusinessException extends ServerException
{
    public function __construct(string $message = '', int $code = ErrorCode::BAD_REQUEST)
    {
        parent::__construct($message, $code);
    }
}
