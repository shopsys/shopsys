<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\Cart;

class CartWithModificationsResultFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @return \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult
     */
    public function create(Cart $cart): CartWithModificationsResult
    {
        return new CartWithModificationsResult($cart);
    }
}
