<?php

declare(strict_types=1);

namespace App\Admin\Request\System;

use App\Common\Request\ApiFormRequest;

class RoleUpdateRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:50'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'enabled' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', 'integer', 'min:0', 'max:999999'],

            'menuIds' => ['sometimes', 'array'],
            'menuIds.*' => ['integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => '角色名称',
            'description' => '描述',
            'enabled' => '是否启用',
            'sort' => '排序',
            'menuIds' => '菜单权限',
        ];
    }
}

