<?php

declare(strict_types=1);

namespace App\ModelFilters;

use HyperfEloquentFilter\ModelFilter as BaseModelFilter;

/**
 * 模型过滤器基类
 * 所有模型过滤器应继承此类
 */
abstract class ModelFilter extends BaseModelFilter
{
    // 可在此添加通用过滤方法或配置
}
