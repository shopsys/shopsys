<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Slug\Exception;

use Overblog\GraphQLBundle\Error\UserError;

//use Shopsys\FrontendApiBundle\Model\Error\UserEntityNotFoundError;
//use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class NoResultFoundForSlugUserError extends UserError //TODO-RK UserEntityNotFoundError implements UserErrorWithCodeInterface
{
    private const CODE = 'no-result-found-for-slug';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
