<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Blog\Category\Exception;

//use Shopsys\FrontendApiBundle\Model\Error\UserEntityNotFoundError;
//use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

use Overblog\GraphQLBundle\Error\UserError;

class BlogCategoryNotFoundUserError extends UserError //TODO-RK UserEntityNotFoundError implements UserErrorWithCodeInterface
{
    private const CODE = 'blog-category-not-found';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
