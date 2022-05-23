<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image\Exception;

use App\FrontendApi\Error\UserErrorWithCodeInterface;
use Overblog\GraphQLBundle\Error\UserError;

class ImageTypeInvalidUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'image-type-invalid';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
