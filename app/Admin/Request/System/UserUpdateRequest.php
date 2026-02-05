<?php

declare(strict_types=1);

namespace App\Admin\Request\System;

use App\Common\Request\ApiFormRequest;

class UserUpdateRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'username' => ['sometimes', 'string', 'max:50'],
            'password' => ['sometimes', 'nullable', 'string', 'min:6', 'max:128'],

            'nickname' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'nullable', 'email', 'max:100'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'gender' => ['sometimes', 'nullable', 'in:male,female,unknown'],
            'status' => ['sometimes', 'integer', 'in:1,2'],
            'avatar' => ['sometimes', 'nullable', 'string', 'max:255'],

            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', 'max:50'],
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => '用户名',
            'password' => '密码',
            'nickname' => '昵称',
            'email' => '邮箱',
            'phone' => '手机号',
            'gender' => '性别',
            'status' => '状态',
            'avatar' => '头像',
            'roles' => '角色',
        ];
    }
}

