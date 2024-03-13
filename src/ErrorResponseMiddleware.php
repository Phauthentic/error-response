<?php

declare(strict_types=1);

namespace Phauthentic\ErrorResponse;

use Exception;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorResponseMiddleware implements MiddlewareInterface
{
    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param ErrorResponseExceptionBasedFactoryInterface $errorResponseFactory
     * @param array<int, string> $exceptionClasses
     * @return void
     */
    public function __construct(
        protected ResponseFactoryInterface $responseFactory,
        protected ErrorResponseExceptionBasedFactoryInterface $errorResponseFactory,
        protected array $exceptionClasses = [Exception::class]
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Exception $exception) {
            if ($this->isAnInterceptableException($exception)) {
                return $this->createResponse(
                    $this->errorResponseFactory->createErrorResponseFromException($exception)
                );
            }

            throw $exception;
        }
    }

    protected function isAnInterceptableException(Exception $exception): bool
    {
        foreach ($this->exceptionClasses as $class) {
            if ($exception instanceof $class) {
                return true;
            }
        }

        return false;
    }

    protected function errorResponseToJson(ErrorResponseInterface $errorResponse): string
    {
        return json_encode($errorResponse, JSON_THROW_ON_ERROR);
    }

    public function createResponse(ErrorResponseInterface $errorResponse): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($errorResponse->getStatus());
        $body = $response->getBody();
        $body->write($this->errorResponseToJson($errorResponse));

        return $response
            ->withStatus($errorResponse->getStatus())
            ->withBody($body)
            ->withHeader('Content-Type', 'application/problem+json');
    }
}
