<?php

declare(strict_types=1);

namespace Phauthentic\ErrorResponse;

use Exception;

interface ErrorResponseExceptionBasedFactoryInterface
{
    public function createErrorResponseFromException(Exception $exception): ErrorResponseInterface;
}
