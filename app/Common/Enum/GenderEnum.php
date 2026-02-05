<?php

declare(strict_types=1);

namespace App\Common\Enum;

/**
 * 性别枚举
 */
enum GenderEnum: string
{
    case Unknown = 'unknown';
    case Male = 'male';
    case Female = 'female';

    /**
     * 获取显示名称
     */
    public function label(): string
    {
        return match ($this) {
            self::Unknown => '未知',
            self::Male => '男',
            self::Female => '女',
        };
    }

    /**
     * 获取前端对应的数值
     */
    public function toFrontend(): string
    {
        return match ($this) {
            self::Unknown => '0',
            self::Male => '1',
            self::Female => '2',
        };
    }

    /**
     * 从前端数值转换
     */
    public static function fromFrontend(string $value): self
    {
        return match ($value) {
            '1', '男' => self::Male,
            '2', '女' => self::Female,
            default => self::Unknown,
        };
    }

    /**
     * 获取所有选项（用于下拉框）
     */
    public static function options(): array
    {
        return [
            ['value' => '0', 'label' => '未知'],
            ['value' => '1', 'label' => '男'],
            ['value' => '2', 'label' => '女'],
        ];
    }
}
