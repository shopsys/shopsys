<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category\Exception;

use Overblog\GraphQLBundle\Error\UserError;

//use Shopsys\FrontendApiBundle\Model\Error\UserEntityNotFoundError;
//use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ReadyCategorySeoMixNotFoundUserError extends UserError //TODO-RK  UserEntityNotFoundError implements UserErrorWithCodeInterface
{
    private const CODE = 'ready-category-seo-mix-not-found';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
