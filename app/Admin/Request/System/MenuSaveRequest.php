<?php

declare(strict_types=1);

namespace App\Admin\Request\System;

use App\Common\Request\ApiFormRequest;

/**
 * 菜单/按钮保存校验（兼容前端字段命名）。
 *
 * 说明：
 * - menuType=button：校验 authName/authLabel/authSort
 * - 其他：校验 name/label/path 等（尽量宽松，避免与前端动态字段不匹配）
 */
class MenuSaveRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'menuType' => ['sometimes', 'string', 'in:menu,button'],
            'parentId' => ['sometimes', 'integer', 'min:0'],

            // menu
            'name' => ['required_without:authName', 'string', 'max:100'],
            'label' => ['sometimes', 'nullable', 'string', 'max:100'],
            'path' => ['sometimes', 'nullable', 'string', 'max:255'],
            'component' => ['sometimes', 'nullable', 'string', 'max:255'],
            'redirect' => ['sometimes', 'nullable', 'string', 'max:255'],
            'icon' => ['sometimes', 'nullable', 'string', 'max:50'],
            'permission' => ['sometimes', 'nullable', 'string', 'max:100'],
            'sort' => ['sometimes', 'integer', 'min:0', 'max:999999'],
            'enabled' => ['sometimes', 'boolean'],
            'isEnable' => ['sometimes', 'boolean'],

            // button
            'authName' => ['required_if:menuType,button', 'string', 'max:100'],
            'authLabel' => ['required_if:menuType,button', 'string', 'max:100'],
            'authSort' => ['sometimes', 'integer', 'min:0', 'max:999999'],
        ];
    }

    public function attributes(): array
    {
        return [
            'menuType' => '类型',
            'parentId' => '父级ID',
            'name' => '名称',
            'label' => '路由名称',
            'path' => '路由路径',
            'component' => '组件路径',
            'redirect' => '重定向',
            'icon' => '图标',
            'permission' => '权限标识',
            'sort' => '排序',
            'enabled' => '是否启用',
            'isEnable' => '是否启用',
            'authName' => '按钮名称',
            'authLabel' => '按钮标识',
            'authSort' => '按钮排序',
        ];
    }
}

