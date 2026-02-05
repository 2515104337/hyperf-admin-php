<?php

declare(strict_types=1);

namespace App\Common\Service\Storage\Engine;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Context\ApplicationContext;

/**
 * 本地存储引擎
 */
class Local extends Server
{
    /**
     * 执行上传
     */
    public function upload(int $fileType): bool
    {
        if (!$this->validate($fileType)) {
            return false;
        }

        $request = ApplicationContext::getContainer()->get(RequestInterface::class);
        $file = $request->file('file');

        if (!$file || !$file->isValid()) {
            $this->error = '上传文件无效';
            return false;
        }

        $extension = strtolower($file->getExtension());
        $savePath = $this->buildSavePath($fileType);
        $saveName = $this->buildSaveName($extension);

        $fullPath = BASE_PATH . '/public/uploads/' . $savePath;

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        try {
            $file->moveTo($fullPath . $saveName);

            if (!$file->isMoved()) {
                $this->error = '文件保存失败';
                return false;
            }

            $this->fileName = 'uploads/' . $savePath . $saveName;
            return true;
        } catch (\Throwable $e) {
            $this->error = '文件上传失败: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * 删除文件
     */
    public function delete(string $fileName): bool
    {
        $filePath = BASE_PATH . '/public/' . $fileName;

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return true;
    }

    /**
     * 获取文件完整URL
     */
    public function getFileUrl(string $fileName): string
    {
        $domain = $this->config['domain'] ?? '';

        if (empty($domain)) {
            return '/' . $fileName;
        }

        return rtrim($domain, '/') . '/' . $fileName;
    }
}
