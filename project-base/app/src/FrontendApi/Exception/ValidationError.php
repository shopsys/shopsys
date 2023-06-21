<?php

declare(strict_types=1);

namespace App\FrontendApi\Exception;

use Overblog\GraphQLBundle\Validator\Exception\ArgumentsValidationException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Throwable;

class ValidationError extends ArgumentsValidationException
{
    /**
     * @param string $message
     * @param string $code
     * @param string $field
     * @param \Throwable|null $previous
     */
    public function __construct(string $message, string $code, string $field, ?Throwable $previous = null)
    {
        parent::__construct(
            new ConstraintViolationList(
                [
                    new ConstraintViolation(
                        $message,
                        null,
                        [],
                        null,
                        $field,
                        null,
                        null,
                        $code,
                    ),
                ],
            ),
            $previous,
        );
    }
}
