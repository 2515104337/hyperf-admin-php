<?php

declare(strict_types=1);

namespace App\Admin\Controller\Setting;

use App\Admin\Controller\BaseController;
use App\Admin\Middleware\AdminAuthMiddleware;
use App\Admin\Middleware\PermissionMiddleware;
use App\Common\Annotation\Permission;
use App\Common\Service\StorageService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/api/setting/storage')]
#[Middlewares([AdminAuthMiddleware::class, PermissionMiddleware::class])]
class StorageController extends BaseController
{
    #[Inject]
    protected StorageService $storageService;

    /**
     * 获取存储引擎列表
     * GET /api/setting/storage/list
     */
    #[GetMapping(path: 'list')]
    #[Permission(code: 'setting:storage:list')]
    public function list(): ResponseInterface
    {
        $list = $this->storageService->getEngineList();
        return $this->response->success($list);
    }

    /**
     * 获取存储引擎配置详情
     * GET /api/setting/storage/detail
     */
    #[GetMapping(path: 'detail')]
    #[Permission(code: 'setting:storage:list')]
    public function detail(): ResponseInterface
    {
        $engine = $this->getParam('engine', 'local');
        $detail = $this->storageService->getEngineDetail($engine);
        return $this->response->success($detail);
    }

    /**
     * 配置存储引擎
     * POST /api/setting/storage/setup
     */
    #[PostMapping(path: 'setup')]
    #[Permission(code: 'setting:storage:edit')]
    public function setup(): ResponseInterface
    {
        $engine = $this->getParam('engine');
        $config = $this->getParam('config', []);

        if (empty($engine)) {
            return $this->response->error('请选择存储引擎');
        }

        $this->storageService->setupEngine($engine, $config);
        return $this->response->success(null, '配置成功');
    }

    /**
     * 切换存储引擎
     * POST /api/setting/storage/change
     */
    #[PostMapping(path: 'change')]
    #[Permission(code: 'setting:storage:edit')]
    public function change(): ResponseInterface
    {
        $engine = $this->getParam('engine');

        if (empty($engine)) {
            return $this->response->error('请选择存储引擎');
        }

        $this->storageService->changeEngine($engine);
        return $this->response->success(null, '切换成功');
    }
}
