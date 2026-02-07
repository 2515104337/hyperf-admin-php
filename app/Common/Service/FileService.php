<?php

declare(strict_types=1);

namespace App\Common\Service;

use App\Common\Model\File\File;
use App\Common\Service\Storage\Driver;

/**
 * 文件服务
 */
class FileService
{
    /**
     * 获取文件分页列表
     */
    public function getList(array $params): array
    {
        $page = (int) ($params['page'] ?? 1);
        $pageSize = (int) ($params['page_size'] ?? 20);

        $query = File::filter($params)->orderBy('id', 'desc');

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->toArray();

        // 添加完整URL
        $list = $this->processFileList($list);

        return [
            'records' => $list,
            'current' => $page,
            'size' => $pageSize,
            'total' => $total,
        ];
    }

    /**
     * 移动文件到指定分类
     */
    public function move(array $ids, int $cid): bool
    {
        return File::whereIn('id', $ids)->update(['cid' => $cid]) > 0;
    }

    /**
     * 重命名文件
     */
    public function rename(int $id, string $name): bool
    {
        $file = File::find($id);
        if (!$file) {
            return false;
        }
        return $file->update(['name' => $name]);
    }

    /**
     * 获取文件完整URL
     */
    public function getFileUrl(string $uri): string
    {
        if (empty($uri)) {
            return '';
        }

        // 如果已经是完整URL，直接返回
        if (str_starts_with($uri, 'http://') || str_starts_with($uri, 'https://')) {
            return $uri;
        }

        $engine = Driver::getEngine();
        return $engine->getFileUrl($uri);
    }

    /**
     * 批量获取文件URL
     */
    public function getFileUrls(array $uris): array
    {
        return array_map(fn($uri) => $this->getFileUrl($uri), $uris);
    }

    /**
     * 处理文件列表，添加完整URL
     */
    public function processFileList(array $files, string $uriField = 'uri'): array
    {
        foreach ($files as &$file) {
            if (isset($file[$uriField])) {
                $file['url'] = $this->getFileUrl($file[$uriField]);
            }
        }
        return $files;
    }
}
