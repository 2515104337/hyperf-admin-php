<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Common\Enum\ErrorCode;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ValidationExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();

        /** @var ValidationException $throwable */
        $errors = $throwable->errors();

        $firstMessage = '参数校验失败';
        foreach ($errors as $messages) {
            if (! empty($messages[0])) {
                $firstMessage = (string) $messages[0];
                break;
            }
        }

        $data = json_encode([
            'code' => ErrorCode::VALIDATION_ERROR,
            'msg' => $firstMessage,
            'data' => [
                'errors' => $errors,
            ],
        ], JSON_UNESCAPED_UNICODE);

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(200)
            ->withBody(new SwooleStream($data));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException;
    }
}

