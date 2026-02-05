<?php

declare(strict_types=1);

namespace App\Admin\Request\System;

use App\Common\Request\ApiFormRequest;

class RoleUpdateMenusRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'menuIds' => ['required', 'array'],
            'menuIds.*' => ['integer', 'min:1'],
        ];
    }

    public function attributes(): array
    {
        return [
            'menuIds' => '菜单权限',
        ];
    }
}

