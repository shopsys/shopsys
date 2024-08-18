<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Customer\Error;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;
use Throwable;

class CustomerUserAccessDeniedUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'access-denied';

    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = 'Access denied to this field.',
        int $code = 403,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
