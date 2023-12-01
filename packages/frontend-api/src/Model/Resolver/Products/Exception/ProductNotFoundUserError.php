<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products\Exception;

use Shopsys\FrontendApiBundle\Model\Error\EntityNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ProductNotFoundUserError extends EntityNotFoundUserError implements UserErrorWithCodeInterface
{
    public const CODE = 'product-not-found';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
