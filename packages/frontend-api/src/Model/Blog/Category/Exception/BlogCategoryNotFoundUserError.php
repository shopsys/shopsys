<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Category\Exception;

use Override;
use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class BlogCategoryNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'blog-category-not-found';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
