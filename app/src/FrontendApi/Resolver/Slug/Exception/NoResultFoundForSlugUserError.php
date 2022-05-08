<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Slug\Exception;

use App\FrontendApi\Error\UserEntityNotFoundError;
use App\FrontendApi\Error\UserErrorWithCodeInterface;

class NoResultFoundForSlugUserError extends UserEntityNotFoundError implements UserErrorWithCodeInterface
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
