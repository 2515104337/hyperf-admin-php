<?php

declare(strict_types=1);

namespace App\Common\Service;

use App\Common\Model\System\Config;

/**
 * 配置服务
 */
class ConfigService
{
    /**
     * 获取配置值
     */
    public function get(string $type, string $name, mixed $default = null): mixed
    {
        return Config::getValue($type, $name, $default);
    }

    /**
     * 设置配置值
     */
    public function set(string $type, string $name, mixed $value): bool
    {
        return Config::setValue($type, $name, $value);
    }

    /**
     * 获取某类型下的所有配置
     */
    public function getByType(string $type): array
    {
        $configs = Config::query()
            ->where('type', $type)
            ->get();

        $result = [];
        foreach ($configs as $config) {
            $decoded = json_decode($config->value, true);
            $result[$config->name] = $decoded === null ? $config->value : $decoded;
        }

        return $result;
    }

    /**
     * 批量设置配置
     */
    public function setMany(string $type, array $configs): bool
    {
        foreach ($configs as $name => $value) {
            if (!Config::setValue($type, $name, $value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 删除配置
     */
    public function delete(string $type, string $name): bool
    {
        return (bool) Config::query()
            ->where('type', $type)
            ->where('name', $name)
            ->delete();
    }

    /**
     * 删除某类型下的所有配置
     */
    public function deleteByType(string $type): bool
    {
        return (bool) Config::query()
            ->where('type', $type)
            ->delete();
    }
}
