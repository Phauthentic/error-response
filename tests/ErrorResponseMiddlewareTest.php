<?php

declare(strict_types=1);

namespace Phauthentic\ErrorResponse\Tests;

use Exception;
use Phauthentic\ErrorResponse\ErrorResponseExceptionBasedFactoryInterface;
use Phauthentic\ErrorResponse\ErrorResponseMiddleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ErrorResponseMiddlewareTest extends TestCase
{
    public function testProcessHandlesUnhandledException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unhandled Exception');

        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $errorResponseFactory = $this->createMock(ErrorResponseExceptionBasedFactoryInterface::class);

        $middleware = new ErrorResponseMiddleware($responseFactory, $errorResponseFactory, [CustomException::class]);

        $request = $this->createMock(ServerRequestInterface::class);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $unhandledException = new Exception('Unhandled Exception');

        $responseFactory->expects($this->never())
            ->method('createResponse');

        $errorResponseFactory->expects($this->never())
            ->method('createErrorResponseFromException');

        $handler->expects($this->once())
            ->method('handle')
            ->with($request)
            ->willThrowException($unhandledException);

        $middleware->process($request, $handler);
    }
}
