<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\HttpFoundation;

use InvalidArgumentException;
use ValueError;

enum HttpMethod: string
{
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
    case HEAD = 'HEAD';
    case OPTIONS = 'OPTIONS';

    /**
     * Get all HTTP method values
     *
     * @return string[]
     */
    public static function getAllMethods(): array
    {
        return array_map(fn (self $method) => $method->value, self::cases());
    }

    public static function getValidHttpMethod(self|string $method): self
    {
        if ($method instanceof self) {
            return $method;
        }

        try {
            return self::from($method);
        } catch (ValueError) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid HTTP method: %s. Valid methods are: %s',
                    $method,
                    implode(', ', self::getAllMethods()),
                ),
            );
        }
    }

    /**
     * @param array<string|self> $methods
     * @throws \InvalidArgumentException
     * @return self[]
     */
    public static function validateMethods(array $methods): array
    {
        if ($methods === []) {
            return []; // Empty array means all methods are allowed
        }

        $normalizedMethods = [];
        $invalidMethods = [];

        foreach ($methods as $method) {
            try {
                $normalizedMethods[] = self::getValidHttpMethod($method);
            } catch (InvalidArgumentException) {
                $invalidMethods[] = (string)$method;
            }
        }

        if ($invalidMethods !== []) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid HTTP methods: %s. Valid methods are: %s',
                    implode(', ', $invalidMethods),
                    implode(', ', self::getAllMethods()),
                ),
            );
        }

        return $normalizedMethods;
    }
}
