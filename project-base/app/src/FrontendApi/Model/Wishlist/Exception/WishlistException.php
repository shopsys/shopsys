<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Wishlist\Exception;

use GraphQL\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class WishlistException extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'invalid-argument';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
