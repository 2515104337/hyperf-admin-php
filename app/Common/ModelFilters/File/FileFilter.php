<?php

declare(strict_types=1);

namespace App\Common\ModelFilters\File;

use App\ModelFilters\ModelFilter;

/**
 * File 模型过滤器
 */
class FileFilter extends ModelFilter
{
    /**
     * 按分类ID筛选
     */
    public function cid(mixed $value): void
    {
        $this->where('cid', (int) $value);
    }

    /**
     * 按类型筛选
     */
    public function type(mixed $value): void
    {
        $this->where('type', (int) $value);
    }

    /**
     * 按来源筛选
     */
    public function source(mixed $value): void
    {
        $this->where('source', (int) $value);
    }

    /**
     * 按来源ID筛选
     */
    public function sourceId(mixed $value): void
    {
        $this->where('source_id', (int) $value);
    }

    /**
     * 按文件名搜索（模糊匹配）
     */
    public function name(string $value): void
    {
        $this->whereLike('name', "%{$value}%");
    }

    /**
     * 按创建时间范围筛选
     */
    public function createdAtStart(string $value): void
    {
        $this->where('created_at', '>=', $value);
    }

    /**
     * 按创建时间范围筛选
     */
    public function createdAtEnd(string $value): void
    {
        $this->where('created_at', '<=', $value);
    }
}
