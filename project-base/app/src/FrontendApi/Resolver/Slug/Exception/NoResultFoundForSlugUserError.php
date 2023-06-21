<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Slug\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class NoResultFoundForSlugUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    private const CODE = 'no-result-found-for-slug';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
