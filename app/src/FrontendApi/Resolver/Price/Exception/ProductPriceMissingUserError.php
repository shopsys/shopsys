<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Price\Exception;

use App\FrontendApi\Error\UserErrorWithCodeInterface;
use Overblog\GraphQLBundle\Error\UserError;

class ProductPriceMissingUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'product-price-missing';

    /**
     * {@inheritDoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
