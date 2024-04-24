<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\AddProductResult;

class AddToCartResult
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult $cartWithModificationsResult
     * @param \Shopsys\FrameworkBundle\Model\Cart\AddProductResult $addProductResult
     */
    public function __construct(
        protected readonly CartWithModificationsResult $cartWithModificationsResult,
        protected readonly AddProductResult $addProductResult,
    ) {
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult
     */
    public function getCart(): CartWithModificationsResult
    {
        return $this->cartWithModificationsResult;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\AddProductResult
     */
    public function getAddProductResult(): AddProductResult
    {
        return $this->addProductResult;
    }
}
