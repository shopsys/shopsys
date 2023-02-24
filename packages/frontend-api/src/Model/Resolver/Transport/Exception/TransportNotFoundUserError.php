<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Transport\Exception;

use Shopsys\FrontendApiBundle\Model\Error\UserEntityNotFoundError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class TransportNotFoundUserError extends UserEntityNotFoundError implements UserErrorWithCodeInterface
{
    protected const CODE = 'transport-not-found';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
