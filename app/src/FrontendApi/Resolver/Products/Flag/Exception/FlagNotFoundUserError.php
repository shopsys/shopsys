<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products\Flag\Exception;

//use Shopsys\FrontendApiBundle\Model\Error\UserEntityNotFoundError;
//use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

use Overblog\GraphQLBundle\Error\UserError;

class FlagNotFoundUserError extends UserError //TODO-RK  UserEntityNotFoundError implements UserErrorWithCodeInterface
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
