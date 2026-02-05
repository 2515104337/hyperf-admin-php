<?php

declare(strict_types=1);

namespace App\Common\Service;

use App\Common\Enum\FileEnum;
use App\Common\Exception\BusinessException;
use App\Common\Model\File\File;
use App\Common\Model\File\FileCate;

/**
 * 文件分类服务
 */
class FileCateService
{
    /**
     * 获取分类列表
     */
    public function getList(int $type = 0): array
    {
        $query = FileCate::query()->orderBy('id', 'asc');

        if ($type > 0) {
            $query->where('type', $type);
        }

        return $query->get()->toArray();
    }

    /**
     * 获取分类树形结构
     */
    public function getTree(int $type = 0): array
    {
        $list = $this->getList($type);
        return $this->buildTree($list);
    }

    /**
     * 构建树形结构
     */
    protected function buildTree(array $list, int $pid = 0): array
    {
        $tree = [];
        foreach ($list as $item) {
            if ($item['pid'] == $pid) {
                $children = $this->buildTree($list, $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        return $tree;
    }

    /**
     * 添加分类
     */
    public function add(array $data): FileCate
    {
        $this->validateData($data);

        // 检查父级是否存在
        if (!empty($data['pid'])) {
            $parent = FileCate::find($data['pid']);
            if (!$parent) {
                throw new BusinessException('父级分类不存在');
            }
        }

        return FileCate::create([
            'pid' => $data['pid'] ?? 0,
            'type' => $data['type'] ?? FileEnum::TYPE_IMAGE,
            'name' => $data['name'],
        ]);
    }

    /**
     * 编辑分类
     */
    public function edit(int $id, array $data): bool
    {
        $cate = FileCate::find($id);

        if (!$cate) {
            throw new BusinessException('分类不存在');
        }

        $this->validateData($data, $id);

        // 检查父级是否存在
        if (!empty($data['pid'])) {
            if ($data['pid'] == $id) {
                throw new BusinessException('父级分类不能是自己');
            }

            $parent = FileCate::find($data['pid']);
            if (!$parent) {
                throw new BusinessException('父级分类不存在');
            }

            // 检查是否形成循环
            if ($this->isChildOf($data['pid'], $id)) {
                throw new BusinessException('不能将分类移动到其子分类下');
            }
        }

        $updateData = [];
        if (isset($data['pid'])) {
            $updateData['pid'] = $data['pid'];
        }
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['type'])) {
            $updateData['type'] = $data['type'];
        }

        return (bool) $cate->update($updateData);
    }

    /**
     * 删除分类
     */
    public function delete(int $id): bool
    {
        $cate = FileCate::find($id);

        if (!$cate) {
            throw new BusinessException('分类不存在');
        }

        // 检查是否有子分类
        $childCount = FileCate::where('pid', $id)->count();
        if ($childCount > 0) {
            throw new BusinessException('该分类下有子分类，无法删除');
        }

        // 检查是否有文件
        $fileCount = File::where('cid', $id)->count();
        if ($fileCount > 0) {
            throw new BusinessException('该分类下有文件，无法删除');
        }

        return (bool) $cate->delete();
    }

    /**
     * 验证数据
     */
    protected function validateData(array $data, ?int $excludeId = null): void
    {
        if (empty($data['name'])) {
            throw new BusinessException('分类名称不能为空');
        }

        // 检查同级下是否有重名
        $query = FileCate::where('name', $data['name'])
            ->where('pid', $data['pid'] ?? 0);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new BusinessException('同级下已存在相同名称的分类');
        }
    }

    /**
     * 检查是否是某分类的子分类
     */
    protected function isChildOf(int $childId, int $parentId): bool
    {
        $child = FileCate::find($childId);

        if (!$child || $child->pid == 0) {
            return false;
        }

        if ($child->pid == $parentId) {
            return true;
        }

        return $this->isChildOf($child->pid, $parentId);
    }
}
