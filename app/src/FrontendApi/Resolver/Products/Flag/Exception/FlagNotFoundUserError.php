<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products\Flag\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class FlagNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    private const CODE = 'flag-not-found';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
