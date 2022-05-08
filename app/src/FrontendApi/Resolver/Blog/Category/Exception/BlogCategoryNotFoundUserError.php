<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Blog\Category\Exception;

use App\FrontendApi\Error\UserEntityNotFoundError;
use App\FrontendApi\Error\UserErrorWithCodeInterface;

class BlogCategoryNotFoundUserError extends UserEntityNotFoundError implements UserErrorWithCodeInterface
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
