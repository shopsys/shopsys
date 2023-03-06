<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Blog\Category\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class BlogCategoryNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
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
