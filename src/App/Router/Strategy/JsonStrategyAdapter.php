<?php

declare(strict_types=1);

namespace Whirlwind\Adapter\League\App\Router\Strategy;

use League\Route\Http;
use League\Route\Strategy\JsonStrategy;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Whirlwind\Infrastructure\Http\Exception\HttpException;
use Whirlwind\Infrastructure\Http\Exception\UnprocessableEntityHttpException;

class JsonStrategyAdapter extends JsonStrategy
{
    protected bool $showDebug;

    public function __construct(ResponseFactoryInterface $responseFactory, bool $showDebug = false, int $jsonFlags = 0)
    {
        parent::__construct($responseFactory, $jsonFlags);
        $this->showDebug = $showDebug;
    }

    protected function buildJsonResponseMiddleware(Http\Exception $exception): MiddlewareInterface
    {
        return new class (
            $this->responseFactory->createResponse(),
            $exception,
            $this->showDebug
        ) implements MiddlewareInterface  {
            protected ResponseInterface $response;
            protected Http\Exception $exception;
            protected bool $showDebug;

            public function __construct(ResponseInterface $response, Http\Exception $exception, bool $showDebug)
            {
                $this->response  = $response->withAddedHeader('content-type', 'application/json');
                $this->exception = $exception;
                $this->showDebug = $showDebug;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                $data = [
                    'message' => $this->exception->getMessage(),
                    'status' => $this->exception->getStatusCode(),
                    'code' => $this->exception->getCode(),
                ];

                if ($this->showDebug) {
                    $data['stackTrace'] = $this->exception->getTraceAsString();
                }

                if ($this->response->getBody()->isWritable()) {
                    $this->response->getBody()->write(\json_encode($data));
                }
                return $this->response->withStatus($this->exception->getStatusCode(), $this->exception->getMessage());
            }
        };
    }

    public function getThrowableHandler(): MiddlewareInterface
    {
        return new class ($this->responseFactory->createResponse(), $this->showDebug) implements MiddlewareInterface
        {
            protected ResponseInterface $response;
            protected bool $showDebug;

            public function __construct(ResponseInterface $response, bool $showDebug)
            {
                $this->response = $response->withAddedHeader('content-type', 'application/json');
                $this->showDebug = $showDebug;
            }

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                try {
                    return $handler->handle($request);
                } catch (UnprocessableEntityHttpException $e) {
                    $data = $e->getErrorCollection();
                    $response = $this->response->withStatus($e->getStatusCode(), $e->getMessage());
                } catch (\Throwable $e) {
                    $data = [
                        'message' => $e->getMessage(),
                        'status' => $e instanceof HttpException ? $e->getStatusCode() : 500,
                        'code' => $e->getCode()
                    ];

                    if ($this->showDebug) {
                        $data['stackTrace'] = $e->getTraceAsString();
                    }
                    $response = $this->response->withStatus($data['status'], $e->getMessage());
                }

                if ($response->getBody()->isWritable()) {
                    $response->getBody()->write(\json_encode($data));
                }

                return $response;
            }
        };
    }

}
