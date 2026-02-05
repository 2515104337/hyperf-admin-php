<?php

declare(strict_types=1);

namespace App\Common\Trait;

use Hyperf\Database\Model\Builder;
use Hyperf\Collection\Collection;

/**
 * 分页 Trait
 * 提供统一的分页处理方法
 */
trait PaginateTrait
{
    /**
     * 分页查询
     *
     * @param Builder $query 查询构造器
     * @param array $params 分页参数 ['current' => 1, 'size' => 10]
     * @param callable|null $formatter 数据格式化回调
     * @return array ['records' => [], 'current' => 1, 'size' => 10, 'total' => 0]
     */
    protected function paginate(Builder $query, array $params, ?callable $formatter = null): array
    {
        $current = (int) ($params['current'] ?? 1);
        $size = (int) ($params['size'] ?? 10);

        // 限制每页最大数量
        $size = min($size, 100);
        $current = max($current, 1);

        $paginator = $query->paginate($size, ['*'], 'page', $current);

        $records = Collection::make($paginator->items());
        if ($formatter) {
            $records = $records->map($formatter);
        }

        return [
            'records' => $records->toArray(),
            'current' => $paginator->currentPage(),
            'size' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
