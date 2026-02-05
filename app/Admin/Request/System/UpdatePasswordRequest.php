<?php

declare(strict_types=1);

namespace App\Admin\Request\System;

use App\Common\Request\ApiFormRequest;

class UpdatePasswordRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'max:128'],
            'newPassword' => ['required', 'string', 'min:6', 'max:128'],
            'confirmPassword' => ['required', 'same:newPassword'],
        ];
    }

    public function attributes(): array
    {
        return [
            'password' => '当前密码',
            'newPassword' => '新密码',
            'confirmPassword' => '确认密码',
        ];
    }
}

