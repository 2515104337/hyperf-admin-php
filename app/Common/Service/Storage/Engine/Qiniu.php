<?php

declare(strict_types=1);

namespace App\Common\Service\Storage\Engine;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Context\ApplicationContext;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

/**
 * 七牛云存储引擎
 */
class Qiniu extends Server
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
        $key = $savePath . $saveName;

        try {
            $auth = new Auth(
                $this->config['access_key'],
                $this->config['secret_key']
            );

            $token = $auth->uploadToken($this->config['bucket']);
            $uploadMgr = new UploadManager();

            [$ret, $err] = $uploadMgr->putFile($token, $key, $file->getRealPath());

            if ($err !== null) {
                $this->error = '七牛云上传失败: ' . $err->message();
                return false;
            }

            $this->fileName = $key;
            return true;
        } catch (\Throwable $e) {
            $this->error = '七牛云上传失败: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * 删除文件
     */
    public function delete(string $fileName): bool
    {
        try {
            $auth = new Auth(
                $this->config['access_key'],
                $this->config['secret_key']
            );

            $bucketMgr = new \Qiniu\Storage\BucketManager($auth);
            [$ret, $err] = $bucketMgr->delete($this->config['bucket'], $fileName);

            if ($err !== null) {
                $this->error = '七牛云删除失败: ' . $err->message();
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            $this->error = '七牛云删除失败: ' . $e->getMessage();
            return false;
        }
    }

    /**
     * 获取文件完整URL
     */
    public function getFileUrl(string $fileName): string
    {
        $domain = $this->config['domain'] ?? '';

        if (empty($domain)) {
            $this->error = '七牛云域名未配置';
            return '';
        }

        return rtrim($domain, '/') . '/' . $fileName;
    }
}
