<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Category\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ReadyCategorySeoMixNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
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
