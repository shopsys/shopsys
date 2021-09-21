<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\Model\Cart\AddProductResult;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class AddToCartResult
{
    /**
     * @var \App\Model\Cart\AddProductResult
     */
    private AddProductResult $addProductResult;

    /**
     * @var \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    protected CartWithModificationsResult $cartWithModifications;

    /**
     * @param \App\FrontendApi\Model\Cart\CartWithModificationsResult $cart
     * @param \App\Model\Cart\AddProductResult $addProductResult
     */
    public function __construct(CartWithModificationsResult $cart, AddProductResult $addProductResult)
    {
        $this->addProductResult = $addProductResult;
        $this->cartWithModifications = $cart;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->cartWithModifications->getUuid();
    }

    /**
     * @return \App\Model\Cart\Item\CartItem[]
     */
    public function getItems(): array
    {
        return $this->cartWithModifications->getItems();
    }

    /**
     * @return \App\Model\Cart\AddProductResult
     */
    public function getAddProductResult(): AddProductResult
    {
        return $this->addProductResult;
    }

    /**
     * @return array<string, array<int, \App\Model\Cart\Item\CartItem>>
     */
    public function getModifications(): array
    {
        return $this->cartWithModifications->getModifications();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalPrice(): Price
    {
        return $this->cartWithModifications->getTotalPrice();
    }
}
