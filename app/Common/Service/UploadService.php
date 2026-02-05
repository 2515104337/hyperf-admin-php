<?php

declare(strict_types=1);

namespace App\Common\Service;

use App\Common\Enum\FileEnum;
use App\Common\Exception\BusinessException;
use App\Common\Model\File\File;
use App\Common\Service\Storage\Driver;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 * 上传服务
 */
class UploadService
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected FileService $fileService;

    /**
     * 允许的 MIME（仅在开启严格校验时生效）
     */
    private const IMAGE_MIMES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/bmp',
        'image/webp',
    ];

    private const VIDEO_MIMES = [
        'video/mp4',
        'video/webm',
        'video/quicktime',
        'video/x-msvideo',
        'video/x-ms-wmv',
        'video/x-flv',
        'video/x-matroska',
    ];

    /**
     * 上传图片
     */
    public function uploadImage(int $cid = 0, int $source = FileEnum::SOURCE_ADMIN, int $sourceId = 0): array
    {
        return $this->upload(FileEnum::TYPE_IMAGE, $cid, $source, $sourceId);
    }

    /**
     * 上传视频
     */
    public function uploadVideo(int $cid = 0, int $source = FileEnum::SOURCE_ADMIN, int $sourceId = 0): array
    {
        return $this->upload(FileEnum::TYPE_VIDEO, $cid, $source, $sourceId);
    }

    /**
     * 上传文件
     */
    public function uploadFile(int $cid = 0, int $source = FileEnum::SOURCE_ADMIN, int $sourceId = 0): array
    {
        return $this->upload(FileEnum::TYPE_FILE, $cid, $source, $sourceId);
    }

    /**
     * 执行上传
     */
    protected function upload(int $fileType, int $cid, int $source, int $sourceId): array
    {
        $file = $this->request->file('file');

        if (!$file || !$file->isValid()) {
            throw new BusinessException('请选择要上传的文件');
        }

        $this->validateFileSize($file);

        $originalName = (string) ($file->getClientFilename() ?: 'file');
        $originalName = $this->sanitizeClientFilename($originalName);

        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        if ($extension === '' && method_exists($file, 'getExtension')) {
            $extension = strtolower((string) $file->getExtension());
        }

        // 验证文件类型
        $this->validateFileType($fileType, $extension);
        $this->validateMimeType($fileType, $file);

        // 获取存储引擎并上传
        $engine = Driver::getEngine();
        $engine->setUploadFile($originalName);

        if (!$engine->upload($fileType)) {
            throw new BusinessException($engine->getError());
        }

        $fileName = $engine->getFileName();

        // 保存文件记录
        $fileRecord = File::create([
            'cid' => $cid,
            'type' => $fileType,
            'name' => $originalName,
            'uri' => $fileName,
            'source' => $source,
            'source_id' => $sourceId,
        ]);

        return [
            'id' => $fileRecord->id,
            'name' => $originalName,
            'uri' => $fileName,
            'url' => $this->fileService->getFileUrl($fileName),
        ];
    }

    /**
     * 校验文件大小（应用层兜底）
     */
    protected function validateFileSize(object $file): void
    {
        $maxMb = (int) (getenv('UPLOAD_MAX_SIZE_MB') ?: 128);
        if ($maxMb <= 0) {
            return;
        }

        if (! method_exists($file, 'getSize')) {
            return;
        }

        $size = (int) ($file->getSize() ?? 0);
        if ($size <= 0) {
            return;
        }

        $maxBytes = $maxMb * 1024 * 1024;
        if ($size > $maxBytes) {
            throw new BusinessException(sprintf('文件大小不能超过 %dMB', $maxMb));
        }
    }

    /**
     * 验证文件类型
     */
    protected function validateFileType(int $fileType, string $extension): void
    {
        $allowedExtensions = match ($fileType) {
            FileEnum::TYPE_IMAGE => FileEnum::getImageExtensions(),
            FileEnum::TYPE_VIDEO => FileEnum::getVideoExtensions(),
            default => FileEnum::getFileExtensions(),
        };

        if (!in_array($extension, $allowedExtensions)) {
            $typeName = FileEnum::getTypeName($fileType);
            throw new BusinessException("不支持的{$typeName}格式: {$extension}");
        }
    }

    /**
     * 验证 MIME（默认关闭，避免不同客户端产生误判）
     */
    protected function validateMimeType(int $fileType, object $file): void
    {
        $strict = filter_var((string) (getenv('UPLOAD_STRICT_MIME') ?: 'false'), FILTER_VALIDATE_BOOL);
        if (! $strict) {
            return;
        }

        if (! method_exists($file, 'getClientMediaType')) {
            return;
        }

        $mime = (string) ($file->getClientMediaType() ?? '');
        if ($mime === '') {
            return;
        }

        $allowed = match ($fileType) {
            FileEnum::TYPE_IMAGE => self::IMAGE_MIMES,
            FileEnum::TYPE_VIDEO => self::VIDEO_MIMES,
            default => [],
        };

        if ($allowed !== [] && !in_array($mime, $allowed, true)) {
            $typeName = FileEnum::getTypeName($fileType);
            throw new BusinessException("不支持的{$typeName} MIME: {$mime}");
        }
    }

    /**
     * 清理客户端文件名，避免路径穿越/不可见字符等问题
     */
    protected function sanitizeClientFilename(string $name): string
    {
        $name = str_replace(["\0", "\r", "\n"], '', $name);
        $name = str_replace('\\', '/', $name);
        $name = basename($name);

        // 仅保留常见安全字符，其他用下划线替换
        $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name) ?: 'file';

        // 限制长度（保留扩展名）
        if (strlen($name) > 200) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $base = pathinfo($name, PATHINFO_FILENAME);
            $base = substr($base, 0, 200 - (strlen($ext) ? (strlen($ext) + 1) : 0));
            $name = $ext !== '' ? ($base . '.' . $ext) : $base;
        }

        return $name;
    }

    /**
     * 删除文件
     */
    public function deleteFile(int $fileId): bool
    {
        $file = File::find($fileId);

        if (!$file) {
            throw new BusinessException('文件不存在');
        }

        // 从存储引擎删除
        $engine = Driver::getEngine();
        $engine->delete($file->uri);

        // 软删除记录
        return (bool) $file->delete();
    }
}
