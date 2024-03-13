# RFC 9457: Problem Details for HTTP APIs

![PHP >= 8.1](https://img.shields.io/static/v1?label=PHP&message=^8.1&color=787CB5&style=for-the-badge&logo=php)
![phpstan Level 8](https://img.shields.io/static/v1?label=phpstan&message=Level%208&color=%3CCOLOR%3E&style=for-the-badge)
![License: MIT](https://img.shields.io/static/v1?label=License&message=MIT&color=%3CCOLOR%3E&style=for-the-badge)

This library is an implementation of [RFC 9457](https://www.rfc-editor.org/rfc/rfc9457.html).

---

HTTP status codes cannot always convey enough information about errors to be helpful. While humans using web browsers can often understand an HTML response content, non-human consumers of HTTP APIs have difficulty doing so.

To address that shortcoming, [RFC 9457](https://www.rfc-editor.org/rfc/rfc9457.html) defines simple JSON and XML document formats to describe the specifics of a problem encountered.

For example, consider a response indicating that the client's account doesn't have enough credit. The API's designer might decide to use the 403 Forbidden status code to inform generic HTTP software (such as client libraries, caches, and proxies) of the response's general semantics. API-specific problem details (such as why the server refused the request and the applicable account balance) can be carried in the response content so that the client can act upon them appropriately (for example, triggering a transfer of more credit into the account).

```text
HTTP/1.1 403 Forbidden
Content-Type: application/problem+json
Content-Language: en

{
 "type": "https://example.com/probs/out-of-credit",
 "title": "You do not have enough credit.",
 "detail": "Your current balance is 30, but that costs 50.",
 "instance": "/account/12345/msgs/abc",
 "balance": 30,
 "accounts": ["/account/12345",
              "/account/67890"]
}
```

## Installation

```sh
composer require phauthentic/error-response
```

## Documentation

It is recommended to read the [RFC 9457](https://www.rfc-editor.org/rfc/rfc9457.html) at least briefly to understand the RFC and how the actual implementation helps you.

### Middleware

The `ErrorResponseMiddleware` is a PSR-15 middleware designed to handle exceptions thrown during the execution of a request and convert them into RFC 9457 conforming error responses.

The middleware takes two argumens:

1. A PSR7 Response Factory implementing `Psr\Http\Message\ResponseFactoryInterface`.
2. An array of exception class names that the middleware should intercept. If an exception is an instance of any of these classes, it will be converted into an error response.

```php
$middleware = new ErrorResponseMiddleware(
    new Psr7ResponseFactory(),
    [
        MyCustomException::class,
        OtherExceptionClass::class
    ]
);
```

Use a proper class hierarchy for your exceptions! For example, have a `DatabaseAccessLayerException` from which you derive sub-types instead of declaring hundreds of exception classes and pass them to the middleware.

### Error Responses

Error Responses can be constructed using one of the provided factories or by instantiating the `ErrorResponse` directly.

```php
$this->errorResponseFactory->createJsonResponseFromError(
    new ErrorResponse(
        status: 403,
        type: 'https://example.com/probs/out-of-credit',
        title: 'You do not have enough credit.',
        extensions: [
            'balance' => 30,
            'accounts' => [
                "/account/12345",
                "/account/67890"
            ]
        ]
    );
);
```

## License

Copyright Florian Krämer

Licensed under the [MIT license](LICENSE.txt).
