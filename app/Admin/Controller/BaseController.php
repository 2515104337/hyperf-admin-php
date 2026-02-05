<?php

declare(strict_types=1);

namespace App\Admin\Controller;

use App\Common\Helper\ResponseHelper;
use Hyperf\Context\Context;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * 管理后台控制器基类
 */
abstract class BaseController
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseHelper $response;

    /**
     * 获取当前登录用户ID
     */
    protected function getUserId(): int
    {
        return (int) Context::get('admin_user_id', 0);
    }

    /**
     * 获取当前登录用户名
     */
    protected function getUsername(): string
    {
        return (string) Context::get('admin_username', '');
    }

    /**
     * 获取请求参数
     */
    protected function getParams(): array
    {
        return $this->request->all();
    }

    /**
     * 获取单个请求参数
     */
    protected function getParam(string $key, mixed $default = null): mixed
    {
        return $this->request->input($key, $default);
    }
}
