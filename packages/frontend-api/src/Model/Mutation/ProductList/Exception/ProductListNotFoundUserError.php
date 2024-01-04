<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnumInterface;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ProductListNotFoundUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'product-list-not-found';

    /**
     * @param string $message
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     */
    public function __construct(
        string $message,
        protected readonly ProductListTypeEnumInterface $productListType,
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
