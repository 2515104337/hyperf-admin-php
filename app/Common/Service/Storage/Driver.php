<?php

declare(strict_types=1);

namespace App\Common\Service\Storage;

use App\Common\Model\System\Config;
use App\Common\Service\Storage\Engine\Aliyun;
use App\Common\Service\Storage\Engine\Local;
use App\Common\Service\Storage\Engine\Qcloud;
use App\Common\Service\Storage\Engine\Qiniu;
use App\Common\Service\Storage\Engine\Server;

/**
 * 存储驱动工厂
 */
class Driver
{
    /**
     * 存储引擎列表
     */
    public const ENGINE_LOCAL = 'local';
    public const ENGINE_ALIYUN = 'aliyun';
    public const ENGINE_QINIU = 'qiniu';
    public const ENGINE_QCLOUD = 'qcloud';

    /**
     * 获取存储引擎实例
     */
    public static function getEngine(?string $engine = null): Server
    {
        if ($engine === null) {
            $engine = self::getDefaultEngine();
        }

        $config = self::getEngineConfig($engine);

        $instance = match ($engine) {
            self::ENGINE_ALIYUN => new Aliyun(),
            self::ENGINE_QINIU => new Qiniu(),
            self::ENGINE_QCLOUD => new Qcloud(),
            default => new Local(),
        };

        return $instance->setConfig($config);
    }

    /**
     * 获取默认存储引擎
     */
    public static function getDefaultEngine(): string
    {
        return Config::getValue('storage', 'default', self::ENGINE_LOCAL);
    }

    /**
     * 设置默认存储引擎
     */
    public static function setDefaultEngine(string $engine): bool
    {
        return Config::setValue('storage', 'default', $engine);
    }

    /**
     * 获取存储引擎配置
     */
    public static function getEngineConfig(string $engine): array
    {
        return Config::getValue('storage', $engine, []);
    }

    /**
     * 设置存储引擎配置
     */
    public static function setEngineConfig(string $engine, array $config): bool
    {
        return Config::setValue('storage', $engine, $config);
    }

    /**
     * 获取所有存储引擎列表
     */
    public static function getEngineList(): array
    {
        return [
            [
                'key' => self::ENGINE_LOCAL,
                'name' => '本地存储',
                'desc' => '文件存储在本地服务器',
            ],
            [
                'key' => self::ENGINE_ALIYUN,
                'name' => '阿里云OSS',
                'desc' => '阿里云对象存储服务',
            ],
            [
                'key' => self::ENGINE_QINIU,
                'name' => '七牛云',
                'desc' => '七牛云对象存储服务',
            ],
            [
                'key' => self::ENGINE_QCLOUD,
                'name' => '腾讯云COS',
                'desc' => '腾讯云对象存储服务',
            ],
        ];
    }

    /**
     * 获取存储引擎配置字段
     */
    public static function getEngineFields(string $engine): array
    {
        return match ($engine) {
            self::ENGINE_LOCAL => [
                ['key' => 'domain', 'name' => '访问域名', 'type' => 'text', 'placeholder' => '例如: https://www.example.com'],
            ],
            self::ENGINE_ALIYUN => [
                ['key' => 'bucket', 'name' => 'Bucket', 'type' => 'text', 'placeholder' => '存储空间名称'],
                ['key' => 'access_key_id', 'name' => 'AccessKeyId', 'type' => 'text', 'placeholder' => ''],
                ['key' => 'access_key_secret', 'name' => 'AccessKeySecret', 'type' => 'password', 'placeholder' => ''],
                ['key' => 'endpoint', 'name' => 'Endpoint', 'type' => 'text', 'placeholder' => '例如: oss-cn-hangzhou.aliyuncs.com'],
                ['key' => 'domain', 'name' => '自定义域名', 'type' => 'text', 'placeholder' => '可选，例如: https://cdn.example.com'],
            ],
            self::ENGINE_QINIU => [
                ['key' => 'bucket', 'name' => 'Bucket', 'type' => 'text', 'placeholder' => '存储空间名称'],
                ['key' => 'access_key', 'name' => 'AccessKey', 'type' => 'text', 'placeholder' => ''],
                ['key' => 'secret_key', 'name' => 'SecretKey', 'type' => 'password', 'placeholder' => ''],
                ['key' => 'domain', 'name' => '访问域名', 'type' => 'text', 'placeholder' => '例如: https://cdn.example.com'],
            ],
            self::ENGINE_QCLOUD => [
                ['key' => 'bucket', 'name' => 'Bucket', 'type' => 'text', 'placeholder' => '存储桶名称，例如: bucket-1250000000'],
                ['key' => 'region', 'name' => 'Region', 'type' => 'text', 'placeholder' => '例如: ap-guangzhou'],
                ['key' => 'secret_id', 'name' => 'SecretId', 'type' => 'text', 'placeholder' => ''],
                ['key' => 'secret_key', 'name' => 'SecretKey', 'type' => 'password', 'placeholder' => ''],
                ['key' => 'domain', 'name' => '自定义域名', 'type' => 'text', 'placeholder' => '可选，例如: https://cdn.example.com'],
            ],
            default => [],
        };
    }
}
