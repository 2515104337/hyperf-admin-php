<?php

declare(strict_types=1);

namespace App\Common\Model\System;

/**
 * 配置模型
 *
 * @property int $id
 * @property string $type
 * @property string $name
 * @property string|null $value
 * @property string $created_at
 * @property string $updated_at
 */
class Config extends Model
{
    protected ?string $table = 'config';

    protected array $fillable = [
        'type',
        'name',
        'value',
    ];

    protected array $casts = [
        'id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取配置值
     */
    public static function getValue(string $type, string $name, mixed $default = null): mixed
    {
        $config = self::query()
            ->where('type', $type)
            ->where('name', $name)
            ->first();

        if (!$config || $config->value === null) {
            return $default;
        }

        $decoded = json_decode($config->value, true);
        return $decoded === null ? $config->value : $decoded;
    }

    /**
     * 设置配置值
     */
    public static function setValue(string $type, string $name, mixed $value): bool
    {
        $valueStr = is_array($value) || is_object($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value;

        return (bool) self::query()->updateOrInsert(
            ['type' => $type, 'name' => $name],
            ['value' => $valueStr, 'updated_at' => date('Y-m-d H:i:s')]
        );
    }
}
