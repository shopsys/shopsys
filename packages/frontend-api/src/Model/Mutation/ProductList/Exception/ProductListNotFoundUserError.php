<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception;

use Overblog\GraphQLBundle\Error\UserError;
use Override;
use Shopsys\FrontendApiBundle\Model\Error\UserErrorWithCodeInterface;

class ProductListNotFoundUserError extends UserError implements UserErrorWithCodeInterface
{
    protected const CODE = 'product-list-not-found';

    public function __construct(
        string $message,
        protected readonly string $productListType,
    ) {
        parent::__construct($message);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getUserErrorCode(): string
    {
        return ProductListUserErrorCodeHelper::getUserErrorCode($this->productListType, static::CODE);
    }
}
