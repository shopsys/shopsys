<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\Model\Cart\AddProductResult;
use Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult;

class AddToCartResult
{
    protected CartWithModificationsResult $cartWithModifications;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult $cart
     * @param \App\Model\Cart\AddProductResult $addProductResult
     */
    public function __construct(CartWithModificationsResult $cart, private AddProductResult $addProductResult)
    {
        $this->cartWithModifications = $cart;
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult
     */
    public function getCart(): CartWithModificationsResult
    {
        return $this->cartWithModifications;
    }

    /**
     * @return \App\Model\Cart\AddProductResult
     */
    public function getAddProductResult(): AddProductResult
    {
        return $this->addProductResult;
    }
}
