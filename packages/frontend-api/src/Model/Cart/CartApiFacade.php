<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrontendApiBundle\Model\Cart\Exception\UnavailableCartUserError;

class CartApiFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFactory $cartFactory
     */
    public function __construct(
        protected readonly CartFacade $cartFacade,
        protected readonly CustomerUserIdentifierFactory $customerUserIdentifierFactory,
        protected readonly CartFactory $cartFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $cartUuid
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart|null
     */
    public function findCart(?CustomerUser $customerUser, ?string $cartUuid): ?Cart
    {
        $this->assertFilledCustomerUserOrUuid($customerUser, $cartUuid);

        if ($customerUser !== null) {
            $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);

            return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
        }

        return $this->getCartByUuid($cartUuid);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $cartUuid
     */
    protected function assertFilledCustomerUserOrUuid(?CustomerUser $customerUser, ?string $cartUuid): void
    {
        if ($customerUser === null && $cartUuid === null) {
            throw new UnavailableCartUserError('Either cart UUID has to be provided, or the user has to be logged in.');
        }
    }

    /**
     * @param string $cartUuid
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    public function getCartByUuid(string $cartUuid): Cart
    {
        $cart = $this->cartFacade->findCartByCartIdentifier($cartUuid);

        if ($cart === null) {
            $cartIdentifier = $this->customerUserIdentifierFactory->getOnlyWithCartIdentifier($cartUuid);
            $cart = $this->cartFactory->create($cartIdentifier);
        }

        return $cart;
    }
}
