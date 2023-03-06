<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ImageSizeInvalidUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
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
