<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Image\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ImageTypeInvalidUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'image-type-invalid';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
