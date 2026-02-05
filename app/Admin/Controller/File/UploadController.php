<?php

declare(strict_types=1);

namespace App\Admin\Controller\File;

use App\Admin\Controller\BaseController;
use App\Admin\Middleware\AdminAuthMiddleware;
use App\Common\Enum\FileEnum;
use App\Common\Service\UploadService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/api/upload')]
#[Middlewares([AdminAuthMiddleware::class])]
class UploadController extends BaseController
{
    #[Inject]
    protected UploadService $uploadService;

    /**
     * 上传图片
     * POST /api/upload/image
     */
    #[PostMapping(path: 'image')]
    public function image(): ResponseInterface
    {
        $cid = (int) $this->getParam('cid', 0);
        $result = $this->uploadService->uploadImage($cid, FileEnum::SOURCE_ADMIN, $this->getUserId());
        return $this->response->success($result);
    }

    /**
     * 上传视频
     * POST /api/upload/video
     */
    #[PostMapping(path: 'video')]
    public function video(): ResponseInterface
    {
        $cid = (int) $this->getParam('cid', 0);
        $result = $this->uploadService->uploadVideo($cid, FileEnum::SOURCE_ADMIN, $this->getUserId());
        return $this->response->success($result);
    }

    /**
     * 上传文件
     * POST /api/upload/file
     */
    #[PostMapping(path: 'file')]
    public function file(): ResponseInterface
    {
        $cid = (int) $this->getParam('cid', 0);
        $result = $this->uploadService->uploadFile($cid, FileEnum::SOURCE_ADMIN, $this->getUserId());
        return $this->response->success($result);
    }
}
