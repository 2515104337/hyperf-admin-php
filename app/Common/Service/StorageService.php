<?php

declare(strict_types=1);

namespace App\Common\Service;

use App\Common\Service\Storage\Driver;

/**
 * 存储设置服务
 */
class StorageService
{
    /**
     * 获取存储引擎列表
     */
    public function getEngineList(): array
    {
        $engines = Driver::getEngineList();
        $default = Driver::getDefaultEngine();

        foreach ($engines as &$engine) {
            $engine['status'] = $engine['key'] === $default ? 1 : 0;
        }

        return $engines;
    }

    /**
     * 获取存储引擎详情
     */
    public function getEngineDetail(string $engine): array
    {
        $fields = Driver::getEngineFields($engine);
        $config = Driver::getEngineConfig($engine);

        // 填充配置值
        foreach ($fields as &$field) {
            $field['value'] = $config[$field['key']] ?? '';
        }

        return [
            'engine' => $engine,
            'fields' => $fields,
        ];
    }

    /**
     * 配置存储引擎
     */
    public function setupEngine(string $engine, array $config): bool
    {
        return Driver::setEngineConfig($engine, $config);
    }

    /**
     * 切换存储引擎
     */
    public function changeEngine(string $engine): bool
    {
        return Driver::setDefaultEngine($engine);
    }

    /**
     * 获取当前存储引擎
     */
    public function getCurrentEngine(): string
    {
        return Driver::getDefaultEngine();
    }
}
