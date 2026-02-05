<?php

declare(strict_types=1);

namespace App\Common\Service\Storage\Engine;

use App\Common\Enum\FileEnum;

/**
 * 存储引擎抽象基类
 */
abstract class Server
{
    /**
     * 存储配置
     */
    protected array $config = [];

    /**
     * 文件信息
     */
    protected array $fileInfo = [];

    /**
     * 错误信息
     */
    protected string $error = '';

    /**
     * 文件名
     */
    protected string $fileName = '';

    /**
     * 设置配置
     */
    public function setConfig(array $config): static
    {
        $this->config = $config;
        return $this;
    }

    /**
     * 设置上传文件信息
     */
    public function setUploadFile(string $name): static
    {
        $this->fileInfo = [
            'name' => $name,
        ];
        return $this;
    }

    /**
     * 设置上传文件信息（从上传文件对象）
     */
    public function setUploadFileInfo(array $fileInfo): static
    {
        $this->fileInfo = $fileInfo;
        return $this;
    }

    /**
     * 获取错误信息
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * 获取文件名
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * 生成文件名
     */
    protected function buildSaveName(string $extension): string
    {
        return date('YmdHis') . substr(md5((string) microtime(true)), 0, 8) . '.' . $extension;
    }

    /**
     * 生成保存路径
     */
    protected function buildSavePath(int $fileType): string
    {
        $dir = FileEnum::getUploadDir($fileType);
        return $dir . '/' . date('Ymd') . '/';
    }

    /**
     * 验证文件
     */
    protected function validate(int $fileType): bool
    {
        $extension = strtolower(pathinfo($this->fileInfo['name'], PATHINFO_EXTENSION));

        $allowedExtensions = match ($fileType) {
            FileEnum::TYPE_IMAGE => FileEnum::getImageExtensions(),
            FileEnum::TYPE_VIDEO => FileEnum::getVideoExtensions(),
            default => FileEnum::getFileExtensions(),
        };

        if (!in_array($extension, $allowedExtensions)) {
            $this->error = '不支持的文件格式: ' . $extension;
            return false;
        }

        return true;
    }

    /**
     * 执行上传
     */
    abstract public function upload(int $fileType): bool;

    /**
     * 删除文件
     */
    abstract public function delete(string $fileName): bool;

    /**
     * 获取文件完整URL
     */
    abstract public function getFileUrl(string $fileName): string;
}
