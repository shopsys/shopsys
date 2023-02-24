<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Blog\Article\Exception;

use Overblog\GraphQLBundle\Error\UserError;

//use Shopsys\FrontendApiBundle\Model\Error\UserEntityNotFoundError;
//use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class BlogArticleNotFoundUserError extends UserError //TODO-RK  UserEntityNotFoundError implements UserErrorWithCodeInterface
{
    private const CODE = 'blog-article-not-found';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
