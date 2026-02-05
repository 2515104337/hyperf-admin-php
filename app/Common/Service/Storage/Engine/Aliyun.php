<?php

declare(strict_types=1);

namespace App\Common\Service\Storage\Engine;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Context\ApplicationContext;
use OSS\OssClient;
use OSS\Core\OssException;

/**
 * 阿里云OSS存储引擎
 */
class Aliyun extends Server
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
        $objectName = $savePath . $saveName;

        try {
            $ossClient = new OssClient(
                $this->config['access_key_id'],
                $this->config['access_key_secret'],
                $this->config['endpoint']
            );

            $ossClient->uploadFile(
                $this->config['bucket'],
                $objectName,
                $file->getRealPath()
            );

            $this->fileName = $objectName;
            return true;
        } catch (OssException $e) {
            $this->error = '阿里云OSS上传失败: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * 删除文件
     */
    public function delete(string $fileName): bool
    {
        try {
            $ossClient = new OssClient(
                $this->config['access_key_id'],
                $this->config['access_key_secret'],
                $this->config['endpoint']
            );

            $ossClient->deleteObject($this->config['bucket'], $fileName);
            return true;
        } catch (OssException $e) {
            $this->error = '阿里云OSS删除失败: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * 获取文件完整URL
     */
    public function getFileUrl(string $fileName): string
    {
        $domain = $this->config['domain'] ?? '';

        if (!empty($domain)) {
            return rtrim($domain, '/') . '/' . $fileName;
        }

        return 'https://' . $this->config['bucket'] . '.' . $this->config['endpoint'] . '/' . $fileName;
    }
}
