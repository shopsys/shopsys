<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ProductAlreadyInListUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'product-already-in-list';

    /**
     * @param string $message
     * @param string $productListType
     */
    public function __construct(
        string $message,
        protected readonly string $productListType,
    ) {
        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     */
    public function getUserErrorCode(): string
    {
        return ProductListUserErrorCodeHelper::getUserErrorCode($this->productListType, static::CODE);
    }
}
