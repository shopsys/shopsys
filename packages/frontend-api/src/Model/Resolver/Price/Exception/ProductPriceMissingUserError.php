<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ProductPriceMissingUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'product-price-missing';

    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
