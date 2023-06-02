<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Price\Exception;

use Overblog\GraphQLBundle\Error\UserError;

use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ProductPriceMissingUserError extends UserError implements UserErrorWithCodeInterface
{
    private const CODE = 'product-price-missing';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return self::CODE;
    }
}
