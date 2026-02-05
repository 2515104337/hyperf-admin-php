<?php

declare(strict_types=1);

namespace App\Common\Enum;

/**
 * 文件类型枚举
 */
class FileEnum
{
    // 文件类型
    public const TYPE_IMAGE = 10;
    public const TYPE_VIDEO = 20;
    public const TYPE_FILE = 30;

    // 文件来源
    public const SOURCE_ADMIN = 0;
    public const SOURCE_USER = 1;

    /**
     * 获取文件类型名称
     */
    public static function getTypeName(int $type): string
    {
        return match ($type) {
            self::TYPE_IMAGE => '图片',
            self::TYPE_VIDEO => '视频',
            self::TYPE_FILE => '文件',
            default => '未知',
        };
    }

    /**
     * 获取文件类型列表
     */
    public static function getTypeList(): array
    {
        return [
            ['value' => self::TYPE_IMAGE, 'label' => '图片'],
            ['value' => self::TYPE_VIDEO, 'label' => '视频'],
            ['value' => self::TYPE_FILE, 'label' => '文件'],
        ];
    }

    /**
     * 获取文件来源名称
     */
    public static function getSourceName(int $source): string
    {
        return match ($source) {
            self::SOURCE_ADMIN => '后台',
            self::SOURCE_USER => '用户',
            default => '未知',
        };
    }

    /**
     * 获取允许的图片扩展名
     */
    public static function getImageExtensions(): array
    {
        return ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    }

    /**
     * 获取允许的视频扩展名
     */
    public static function getVideoExtensions(): array
    {
        return ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'];
    }

    /**
     * 获取允许的文件扩展名
     */
    public static function getFileExtensions(): array
    {
        return ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'zip', 'rar', '7z'];
    }

    /**
     * 根据扩展名获取文件类型
     */
    public static function getTypeByExtension(string $extension): int
    {
        $extension = strtolower($extension);

        if (in_array($extension, self::getImageExtensions())) {
            return self::TYPE_IMAGE;
        }

        if (in_array($extension, self::getVideoExtensions())) {
            return self::TYPE_VIDEO;
        }

        return self::TYPE_FILE;
    }

    /**
     * 获取上传目录
     */
    public static function getUploadDir(int $type): string
    {
        return match ($type) {
            self::TYPE_IMAGE => 'images',
            self::TYPE_VIDEO => 'video',
            default => 'file',
        };
    }
}
