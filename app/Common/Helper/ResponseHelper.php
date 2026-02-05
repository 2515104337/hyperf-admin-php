<?php

declare(strict_types=1);

namespace App\Common\Helper;

use App\Common\Enum\ErrorCode;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class ResponseHelper
{
    public function __construct(
        protected ResponseInterface $response
    ) {}

    /**
     * 成功响应
     */
    public function success(mixed $data = null, string $msg = 'success'): PsrResponseInterface
    {
        return $this->response->json([
            'code' => ErrorCode::OK,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    /**
     * 分页响应
     */
    public function paginate(array $records, int $current, int $size, int $total): PsrResponseInterface
    {
        return $this->success([
            'records' => $records,
            'current' => $current,
            'size' => $size,
            'total' => $total,
        ]);
    }

    /**
     * 错误响应
     */
    public function error(string $msg = 'error', int $code = ErrorCode::BAD_REQUEST, mixed $data = null): PsrResponseInterface
    {
        return $this->response->json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
        ]);
    }

    /**
     * 未授权响应
     */
    public function unauthorized(string $msg = '未授权，请重新登录'): PsrResponseInterface
    {
        return $this->error($msg, ErrorCode::UNAUTHORIZED);
    }

    /**
     * 禁止访问响应
     */
    public function forbidden(string $msg = '无权限访问'): PsrResponseInterface
    {
        return $this->error($msg, ErrorCode::FORBIDDEN);
    }
}
