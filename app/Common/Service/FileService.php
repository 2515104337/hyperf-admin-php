<?php

declare(strict_types=1);

namespace App\Common\Service;

use App\Common\Service\Storage\Driver;

/**
 * 文件URL服务
 */
class FileService
{
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
