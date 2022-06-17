<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\Model\Cart\CartMigrationFacade;
use App\Model\Customer\User\CustomerUser;

class MergeCartFacade
{
    /**
     * @var \App\FrontendApi\Model\Cart\CartFacade
     */
    private CartFacade $cartFacade;

    /**
     * @var \App\Model\Cart\CartMigrationFacade
     */
    private CartMigrationFacade $cartMigrationFacade;

    /**
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \App\Model\Cart\CartMigrationFacade $cartMigrationFacade
     */
    public function __construct(
        CartFacade $cartFacade,
        CartMigrationFacade $cartMigrationFacade
    ) {
        $this->cartFacade = $cartFacade;
        $this->cartMigrationFacade = $cartMigrationFacade;
    }

    /**
     * @param string $cartUuid
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    public function mergeCartByUuidToCustomerCart(string $cartUuid, CustomerUser $customerUser): void
    {
        $oldCart = $this->cartFacade->getCartCreateIfNotExists(null, $cartUuid);
        $customerCart = $this->cartFacade->getCartCreateIfNotExists($customerUser, null);

        $this->cartMigrationFacade->mergeCarts($oldCart, $customerCart);
    }
}
