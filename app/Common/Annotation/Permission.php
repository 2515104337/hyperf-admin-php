<?php

declare(strict_types=1);

namespace App\Common\Annotation;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * 权限注解
 * 用于标记控制器方法需要的权限
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Permission extends AbstractAnnotation
{
    /**
     * @param string $code 权限代码（如 user:add, user:edit）
     */
    public function __construct(
        public string $code = ''
    ) {}
}
