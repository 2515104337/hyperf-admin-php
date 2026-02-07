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
        return $this->response->json([
            'code' => ErrorCode::UNAUTHORIZED,
            'msg' => $msg,
            'data' => null,
        ])->withStatus(401);
    }

    /**
     * 禁止访问响应
     */
/**
 * 禁止访问方法
 * 返回一个403 Forbidden响应，表示用户没有权限访问请求的资源
 *
 * @param string $msg 错误提示信息，默认为'无权限访问'
 * @return PsrResponseInterface 返回一个PSR-7标准的响应对象，包含状态码403和JSON格式的错误信息
 */
    public function forbidden(string $msg = '无权限访问'): PsrResponseInterface
    {
    // 返回JSON响应，包含错误代码、错误提示和空数据，并设置HTTP状态码为403
        return $this->response->json([
            'code' => ErrorCode::FORBIDDEN,  // 使用预定义的错误代码常量
            'msg' => $msg,                   // 自定义或默认的错误提示信息
            'data' => null,                  // 禁止访问时通常不返回数据
        ])->withStatus(403);                // 设置HTTP响应状态码为403 Forbidden
    }
}
