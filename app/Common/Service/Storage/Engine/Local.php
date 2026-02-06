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
        $targetFile = $fullPath . $saveName;

        // 使用 exec 创建目录，避免 Swoole 协程 hook 的 bug
        if (!is_dir($fullPath)) {
            if (extension_loaded('swoole')) {
                @\Swoole\Coroutine\System::exec("mkdir -p " . escapeshellarg($fullPath));
            } else {
                @mkdir($fullPath, 0755, true);
            }
        }

        try {
            // 获取临时文件路径
            $tmpFile = $file->getPathname();

            // 使用 exec mv 移动文件，避免 Swoole 协程 hook 的 bug
            if (extension_loaded('swoole')) {
                $result = @\Swoole\Coroutine\System::exec("mv " . escapeshellarg($tmpFile) . " " . escapeshellarg($targetFile));
                if ($result['code'] !== 0) {
                    $this->error = '文件保存失败';
                    return false;
                }
            } else {
                $file->moveTo($targetFile);
                if (!$file->isMoved()) {
                    $this->error = '文件保存失败';
                    return false;
                }
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
            // 使用 Swoole 的原生文件操作，避免协程 hook 的 bug
            if (extension_loaded('swoole')) {
                return @\Swoole\Coroutine\System::exec("rm -f " . escapeshellarg($filePath))['code'] === 0;
            }
            return @unlink($filePath);
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
