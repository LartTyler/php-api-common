This package contains components that tend to be useful across different REST API projects.

This package is broken up into several parts:

- [Common](#common) code, files that are always useful.
- [Validation](#validation) code, files that are useful in tandem with the `symfony/validator` component.
- [Doctrine Query Document](#doctrine-query-document) code, files that are useful in tandem with the
  `dbstudios/doctrine-query-document` library.
- [Lexik JWT](#lexik-jwt) code, files that are useful in tandem with the `lexik/jwt-authentication-bundle` bundle.

# Common
Common files include the `ResponderInterface` and it's implementations, as well as the classes in the top level of the
[`src/Error/Errors`](src/Error/Errors) directory.

Working with responders is pretty straightforward.

```php
<?php
    $serializer = new \Symfony\Component\Serializer\Serializer(
        [
            new \Symfony\Component\Serializer\Normalizer\ObjectNormalizer(),
        ],
        [
            new \Symfony\Component\Serializer\Encoder\JsonEncoder(),
        ]
    );
    
    $responder = new \DaybreakStudios\RestApiCommon\Responder($serializer);
    
    return $responder->createResponse(
        'json',
        [
            'message' => 'Hello, world!',
        ]
    );
```

The above code returns a Response object containing the following JSON.

```json
{
    "message": "Hello, world!"
}
```

Responders can also be used to simplify error handling, and to normalize error response formats.

```php
<?php
    /** @var \DaybreakStudios\RestApiCommon\ResponderInterface $responder */
    $responder = getResponder();
    
    return $responder->createErrorResponse(
        new \DaybreakStudios\RestApiCommon\Error\Errors\AccessDeniedError(),
        'json'
    );
```

The above code returns a Response object containing the following JSON.

```json
{
    "error": {
        "code": "access_denied",
        "message": "Access Denied"
    }
}
```

For Symfony projects, you can use `DaybreakStudios\RestApiCommon\ResponderService` instead, which takes a responder and
the request stack, allowing you to omit the format argument in calls to `::createResponse()` and
`createErrorResponse()`.

# Validation
The validation component adds an extra API error class, which will normalize constraint violation errors from the
[symfony/validator](https://packagist.org/packages/symfony/validator) component, allowing them to nicely returned as an
API error.

```php
<?php
    /** @var \DaybreakStudios\RestApiCommon\ResponderInterface $responder */
    $responder = getResponder();
    
    /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $violations */
    $violations = getValidationErrors();
    
    return $responder->createErrorResponse(
        new \DaybreakStudios\RestApiCommon\Error\Errors\Validation\ValidationFailedError($violations),
        'json'
    );
```

The above code returns a Response object containing approximately the following JSON.

```json
{
    "error": {
        "code": "validation_failed",
        "message": "One or more fields did not pass validation",
        "context": {
            "failures": {
                "path.to.field": "Symfony validator error message (e.g. 'This value should be 3 or less.')",
                "some.other.field": "Error message"
            }
        }
    }
}
```

# Doctrine Query Document
The Doctrine query document component adds 4 new API error classes. Since they're relatively simple, please refer to
the individual [class documentation](src/Error/Errors/DoctrineQueryDocument).

# Lexik JWT
The Lexik JWT component adds a special event subscriber that will transform the very generic error messages emitted by
the [lexik/jwt-authentication-bundle](https://packagist.org/packages/lexik/jwt-authentication-bundle) into messages that
can be displayed directly to the end-user.

To register the subscriber in a Symfony application, be sure to add the
[`kernel.event_subscriber`](https://symfony.com/doc/current/reference/dic_tags.html#dic-tags-kernel-event-subscriber)
tag to the service!
