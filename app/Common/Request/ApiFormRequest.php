<?php

declare(strict_types=1);

namespace App\Common\Request;

use Hyperf\Validation\Request\FormRequest;

/**
 * API 统一校验基类：
 * - 默认放行 authorize（如需权限控制，仍建议在中间件/业务层处理）
 * - 提供 validatedData() 便于语义化调用
 */
abstract class ApiFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 语义化别名，避免和父类 validated() 冲突理解成本。
     */
    public function validatedData(): array
    {
        return $this->validated();
    }

    /**
     * 取第一条错误消息（用于 msg 展示）。
     */
    public function firstErrorMessage(): string
    {
        $errors = $this->getValidatorInstance()->errors()->messages();
        foreach ($errors as $messages) {
            if (! empty($messages[0])) {
                return (string) $messages[0];
            }
        }
        return '参数校验失败';
    }
}

