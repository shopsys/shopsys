<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ImageSizeInvalidUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'image-size-invalid';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
