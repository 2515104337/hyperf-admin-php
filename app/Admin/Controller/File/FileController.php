<?php

declare(strict_types=1);

namespace App\Admin\Controller\File;

use App\Admin\Controller\BaseController;
use App\Admin\Middleware\AdminAuthMiddleware;
use App\Admin\Middleware\PermissionMiddleware;
use App\Common\Annotation\Permission;
use App\Common\Enum\FileEnum;
use App\Common\Model\File\File;
use App\Common\Service\FileCateService;
use App\Common\Service\FileService;
use App\Common\Service\UploadService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\PostMapping;
use Psr\Http\Message\ResponseInterface;

#[Controller(prefix: '/api/file')]
#[Middlewares([AdminAuthMiddleware::class, PermissionMiddleware::class])]
class FileController extends BaseController
{
    #[Inject]
    protected FileService $fileService;

    #[Inject]
    protected FileCateService $fileCateService;

    #[Inject]
    protected UploadService $uploadService;

    /**
     * 获取文件列表
     * GET /api/file/list
     */
    #[GetMapping(path: 'list')]
    #[Permission(code: 'file:list')]
    public function list(): ResponseInterface
    {
        $params = $this->getParams();
        $page = (int) ($params['page'] ?? 1);
        $pageSize = (int) ($params['page_size'] ?? 20);

        $query = File::filter($params)->orderBy('id', 'desc');

        $total = $query->count();
        $list = $query->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get()
            ->toArray();

        // 添加完整URL
        $list = $this->fileService->processFileList($list);

        return $this->response->paginate($list, $page, $pageSize, $total);
    }

    /**
     * 移动文件
     * POST /api/file/move
     */
    #[PostMapping(path: 'move')]
    #[Permission(code: 'file:edit')]
    public function move(): ResponseInterface
    {
        $ids = $this->getParam('ids', []);
        $cid = (int) $this->getParam('cid', 0);

        if (empty($ids)) {
            return $this->response->error('请选择要移动的文件');
        }

        File::whereIn('id', $ids)->update(['cid' => $cid]);

        return $this->response->success(null, '移动成功');
    }

    /**
     * 重命名文件
     * POST /api/file/rename
     */
    #[PostMapping(path: 'rename')]
    #[Permission(code: 'file:edit')]
    public function rename(): ResponseInterface
    {
        $id = (int) $this->getParam('id');
        $name = $this->getParam('name');

        if (empty($id)) {
            return $this->response->error('请选择要重命名的文件');
        }

        if (empty($name)) {
            return $this->response->error('文件名称不能为空');
        }

        $file = File::find($id);
        if (!$file) {
            return $this->response->error('文件不存在');
        }

        $file->update(['name' => $name]);

        return $this->response->success(null, '重命名成功');
    }

    /**
     * 删除文件
     * POST /api/file/delete
     */
    #[PostMapping(path: 'delete')]
    #[Permission(code: 'file:delete')]
    public function delete(): ResponseInterface
    {
        $ids = $this->getParam('ids', []);

        if (empty($ids)) {
            return $this->response->error('请选择要删除的文件');
        }

        try {
            foreach ($ids as $id) {
                $this->uploadService->deleteFile((int) $id);
            }
        } catch (\Throwable $e) {
            return $this->response->error('删除失败: ' . $e->getMessage());
        }

        return $this->response->success(null, '删除成功');
    }

    /**
     * 获取分类列表
     * GET /api/file/cate/list
     */
    #[GetMapping(path: 'cate/list')]
    #[Permission(code: 'file:list')]
    public function cateList(): ResponseInterface
    {
        $type = (int) $this->getParam('type', 0);
        $list = $this->fileCateService->getTree($type);
        return $this->response->success($list);
    }

    /**
     * 添加分类
     * POST /api/file/cate/add
     */
    #[PostMapping(path: 'cate/add')]
    #[Permission(code: 'file:cate:add')]
    public function cateAdd(): ResponseInterface
    {
        $data = [
            'pid' => (int) $this->getParam('pid', 0),
            'type' => (int) $this->getParam('type', FileEnum::TYPE_IMAGE),
            'name' => $this->getParam('name'),
        ];

        $cate = $this->fileCateService->add($data);

        return $this->response->success(['id' => $cate->id], '添加成功');
    }

    /**
     * 编辑分类
     * POST /api/file/cate/edit
     */
    #[PostMapping(path: 'cate/edit')]
    #[Permission(code: 'file:cate:edit')]
    public function cateEdit(): ResponseInterface
    {
        $id = (int) $this->getParam('id');

        if (empty($id)) {
            return $this->response->error('请选择要编辑的分类');
        }

        $data = [
            'pid' => (int) $this->getParam('pid', 0),
            'name' => $this->getParam('name'),
        ];

        $this->fileCateService->edit($id, $data);

        return $this->response->success(null, '编辑成功');
    }

    /**
     * 删除分类
     * POST /api/file/cate/delete
     */
    #[PostMapping(path: 'cate/delete')]
    #[Permission(code: 'file:cate:delete')]
    public function cateDelete(): ResponseInterface
    {
        $id = (int) $this->getParam('id');

        if (empty($id)) {
            return $this->response->error('请选择要删除的分类');
        }

        $this->fileCateService->delete($id);

        return $this->response->success(null, '删除成功');
    }
}
