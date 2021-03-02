This package contains components that tend to be useful across different REST API projects.

This package is broken up into several parts:

- [Common](#common) code, files that are always useful.
- [Validation](#validation) code, files that are useful in tandem with the `symfony/validator` component.
- [Doctrine Query Document](#doctrine-query-document) code, files that are useful in tandem with the
  `dbstudios/doctrine-query-document` library.
- [Lexik JWT](#lexik-jwt) code, files that are useful in tandem with the `lexik/jwt-authentication-bundle` bundle.
- [Payload](#payload) code, files that are useful in tandem with the `symfony/serializer` component's deserialize
  functionality.

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

# Payload
The payload component adds a simple framework for parsing API payloads into Data Transfer Object (DTO) classes. While
useful by itself, giving you a more concrete set of fields on the objects your API consumes, it is most useful when also
paired with the `symfony/validator` package, giving you a clean way to validate input to your API. By default,
assertions tagged with the "create" group will only be run when `DecoderIntent::CREATE` is passed to the `parse()`
method, while assertions tagged with the "update" method will be run when `DecoderIntent::UPDATE` is passed. Assertions
in the "Default" group will _always_ run. For example:

```php
<?php
    use Symfony\Component\Validator\Constraint as Assert;
    use DaybreakStudios\RestApiCommon\Payload\Decoders\SymfonyDeserializeDecoder;
    use Symfony\Component\Serializer\SerializerInterface;
    use Symfony\Component\Validator\Validator\ValidatorInterface;
    use DaybreakStudios\RestApiCommon\Payload\DecoderIntent;

    class UserPayload {
        /**
         * @Assert\Type("string")
         * @Assert\NotBlank(groups={"create"})
         * 
         * @var string 
         */
        public $name;
        
        /**
         * @Assert\Type("string")
         * @Assert\Email()
         * @Assert\NotBlank(groups={"create"}) 
         * 
         * @var string 
         */
        public $email;
    }
    
    $input = json_encode([
        'name' => 'Tyler Lartonoix',
        'email' => 'invalid email',
    ]);

    /**
     * These objects would come from some other part of your application, e.g. a service container 
     * @var SerializerInterface $serializer
     * @var ValidatorInterface $validator 
     */

    $decoder = new SymfonyDeserializeDecoder($serializer, 'json', UserPayload::class, $validator);
    $payload = $decoder->parse(DecoderIntent::CREATE, $input);
```

In the above example, the final line's call to `SymfonyDeserializeDecoder::parse()` would result in an
`ApiErrorException`, whose `error` field would be a `ValidationFailedError`, since the input payload did not pass
validation (the email address was not a valid email address).

If you're using PHP 8, you can use the `PayloadTrait` in your DTO class to gain access to the `exists()` and `unset()`
utility methods. Since PHP 8 introduces the `mixed` psuedo-type, you can use it as the actual type for the properties on
your DTO class. By doing so, you can use the `exists()` method, which uses `\ReflectionProperty::isInitialized()` to
determine if a property was actually part of the input payload, and not just defaulting to `null` because the key was
not included. This is useful when you have a property that might be part of the payload, but whose value could be `null`
(since `isset()` would still return `false` when used on such a property). Since `exists()` uses reflection, results
are cached to mitigate performance hits due to repeated calls for the same property. **Do not** call `unset()` directly
on any property of a DTO class that you might need to call `exists()` on; instead, use `PayloadTrait::unset()` to unset
the property and clear it from the `exists()` cache.
