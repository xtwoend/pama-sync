<?php

namespace App\Exception\Handler;

use Throwable;
use Hyperf\Utils\Codec\Json;
use Psr\Http\Message\ResponseInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Validation\ValidationException;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\ExceptionHandler\ExceptionHandler;

class ValidationExceptionHandler extends ExceptionHandler
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        $this->stopPropagation();

        $payload = [
            'error' => 422,
            'message' => $throwable->getMessage(),
            'data' => $throwable->errors()
        ];

        return $response
            ->withStatus(422)
            ->withHeader('Server', 'Pama')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new SwooleStream(Json::encode($payload)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException;
    }
}