<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Exception\Handler;

use Throwable;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\ExceptionHandler\ExceptionHandler;

class AppExceptionHandler extends ExceptionHandler
{
    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());

        $err_code = $throwable->getCode();

        $payload = [
            'error' => $err_code,
            'message' => $throwable->getMessage()
        ];

        if (config('app_env') == 'dev') {
            $payload['throw'] = $throwable->getTrace();
        }

        return $response
            ->withStatus(200)
            ->withHeader('Server', 'Pama')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new SwooleStream(Json::encode($payload)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
