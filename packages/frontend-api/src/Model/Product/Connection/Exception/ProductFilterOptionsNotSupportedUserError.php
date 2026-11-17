<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Connection\Exception;

use GraphQL\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ProductFilterOptionsNotSupportedUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'product-filter-options-not-supported';

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
