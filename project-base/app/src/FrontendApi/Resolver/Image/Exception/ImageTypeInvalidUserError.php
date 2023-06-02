<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Image\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ImageTypeInvalidUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    private const CODE = 'image-type-invalid';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
