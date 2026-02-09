<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\AddProductResult;

class AddToCartResult
{
    public function __construct(
        protected readonly CartWithModificationsResult $cartWithModificationsResult,
        protected readonly AddProductResult $addProductResult,
    ) {
    }

    public function getCart(): CartWithModificationsResult
    {
        return $this->cartWithModificationsResult;
    }

    public function getAddProductResult(): AddProductResult
    {
        return $this->addProductResult;
    }
}
