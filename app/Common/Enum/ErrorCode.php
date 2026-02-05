<?php

declare(strict_types=1);

namespace App\Common\Enum;

/**
 * 业务错误码约定：
 * - HTTP 层依然统一返回 200（由异常处理器保证），前端/调用方以 JSON 的 code 判定业务状态。
 * - 与 HTTP 状态码保持一致的语义，降低理解成本。
 */
final class ErrorCode
{
    public const OK = 200;

    public const BAD_REQUEST = 400;
    public const UNAUTHORIZED = 401;
    public const FORBIDDEN = 403;
    public const VALIDATION_ERROR = 422;

    public const INTERNAL_ERROR = 500;
}

