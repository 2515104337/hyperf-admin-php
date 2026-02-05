<?php

declare(strict_types=1);

namespace App\Admin\Middleware;

use App\Common\Helper\ResponseHelper;
use Hyperf\Logger\LoggerFactory;
use Phper666\JWTAuth\JWT;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Context\Context;
use Psr\Log\LoggerInterface;

class AdminAuthMiddleware implements MiddlewareInterface
{
    protected LoggerInterface $logger;

    public function __construct(
        protected JWT $jwt,
        protected ResponseHelper $response,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->get('default');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // 从 Header 获取 token
        $token = $request->getHeaderLine('Authorization');
        if (empty($token)) {
            return $this->response->unauthorized('缺少 Authorization 请求头');
        }

        // 移除 Bearer 前缀（如果有）
        $token = str_replace('Bearer ', '', $token);

        // 验证 token（只捕获认证相关异常）
        try {
            if (!$this->jwt->verifyToken($token)) {
                return $this->response->unauthorized('Token 无效或已过期');
            }

            // 获取用户信息并存入上下文
            $claims = $this->jwt->getClaimsByToken($token);
            Context::set('admin_user_id', (int) ($claims['uid'] ?? 0));
            Context::set('admin_username', $claims['username'] ?? '');
        } catch (\Throwable $e) {
            // 记录认证错误日志
            $this->logger->error(sprintf(
                '[AdminAuthMiddleware] %s[%s] in %s',
                $e->getMessage(),
                $e->getLine(),
                $e->getFile()
            ));

            return $this->response->unauthorized('Token 验证失败');
        }

        // 继续处理请求（异常由全局异常处理器处理）
        return $handler->handle($request);
    }
}
