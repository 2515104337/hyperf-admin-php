<?php

declare(strict_types=1);

namespace App\Common\Service\Storage\Engine;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Context\ApplicationContext;
use Qcloud\Cos\Client;

/**
 * 腾讯云COS存储引擎
 */
class Qcloud extends Server
{
    /**
     * 获取COS客户端
     */
    protected function getClient(): Client
    {
        return new Client([
            'region' => $this->config['region'],
            'schema' => 'https',
            'credentials' => [
                'secretId' => $this->config['secret_id'],
                'secretKey' => $this->config['secret_key'],
            ],
        ]);
    }

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
        $key = $savePath . $saveName;

        try {
            $cosClient = $this->getClient();

            $cosClient->upload(
                $this->config['bucket'],
                $key,
                fopen($file->getRealPath(), 'rb')
            );

            $this->fileName = $key;
            return true;
        } catch (\Throwable $e) {
            $this->error = '腾讯云COS上传失败: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * 删除文件
     */
    public function delete(string $fileName): bool
    {
        try {
            $cosClient = $this->getClient();

            $cosClient->deleteObject([
                'Bucket' => $this->config['bucket'],
                'Key' => $fileName,
            ]);

            return true;
        } catch (\Throwable $e) {
            $this->error = '腾讯云COS删除失败: ' . $e->getMessage();
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

        return 'https://' . $this->config['bucket'] . '.cos.' . $this->config['region'] . '.myqcloud.com/' . $fileName;
    }
}
