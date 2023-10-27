<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ProductListNotFoundUserError extends UserError implements UserErrorWithCodeInterface
{
    public const CODE = 'product-list-not-found';

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return static::CODE;
    }
}
