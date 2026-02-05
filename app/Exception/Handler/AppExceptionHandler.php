<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Common\Enum\ErrorCode;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Logger\LoggerFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class AppExceptionHandler extends ExceptionHandler
{
    protected LoggerInterface $fileLogger;

    public function __construct(
        protected StdoutLoggerInterface $logger,
        LoggerFactory $loggerFactory
    ) {
        $this->fileLogger = $loggerFactory->get('default');
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $errorMessage = sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile());
        $traceString = $throwable->getTraceAsString();

        // 输出到控制台
        $this->logger->error($errorMessage);
        $this->logger->error($traceString);

        // 记录到文件
        $this->fileLogger->error($errorMessage);
        $this->fileLogger->error($traceString);

        // 返回 JSON 格式错误响应
        $data = json_encode([
            'code' => ErrorCode::INTERNAL_ERROR,
            'msg' => '服务器内部错误',
            'data' => null,
        ], JSON_UNESCAPED_UNICODE);

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus(200)
            ->withBody(new SwooleStream($data));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
