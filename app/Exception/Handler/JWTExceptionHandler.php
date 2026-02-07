<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Common\Enum\ErrorCode;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Phper666\JWTAuth\Exception\JWTException;
use Phper666\JWTAuth\Exception\TokenValidException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * JWT 异常处理器
 * 将 JWT 验证失败的异常转换为 401 未授权响应
 */
class JWTExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();

        $data = json_encode([
            'code' => ErrorCode::UNAUTHORIZED,
            'msg' => 'Token 无效或已过期',
            'data' => null,
        ], JSON_UNESCAPED_UNICODE);

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(401)
            ->withBody(new SwooleStream($data));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof TokenValidException
            || $throwable instanceof JWTException;
    }
}
