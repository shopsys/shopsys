<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\FrontendApi\Model\Product\ProductFacade;
use App\Model\Cart\Cart;
use App\Model\Cart\CartFacade;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserIdentifierFactory;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;

class AddToCartFacade
{
    /**
     * @var \App\Model\Cart\CartFacade
     */
    protected CartFacade $cartFacade;

    /**
     * @var \App\FrontendApi\Model\Product\ProductFacade
     */
    protected ProductFacade $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \App\Model\Customer\User\CustomerUserIdentifierFactory
     */
    protected CustomerUserIdentifierFactory $customerUserIdentifierFactory;

    /**
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \App\FrontendApi\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     */
    public function __construct(
        CartFacade $cartFacade,
        ProductFacade $productFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        CustomerUserIdentifierFactory $customerUserIdentifierFactory
    ) {
        $this->cartFacade = $cartFacade;
        $this->productFacade = $productFacade;
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->customerUserIdentifierFactory = $customerUserIdentifierFactory;
    }

    /**
     * @param string $productUuid
     * @param int $quantity
     * @param bool $isAbsoluteQuantity
     * @param \App\Model\Cart\Cart $cart
     */
    public function addProductByUuidToCart(string $productUuid, int $quantity, bool $isAbsoluteQuantity, Cart $cart): void
    {
        try {
            $product = $this->productFacade->getSellableByUuid(
                $productUuid,
                $this->domain->getId(),
                $this->currentCustomerUser->getPricingGroup()
            );
        } catch (ProductNotFoundException $exception) {
            throw new UserError(sprintf('Product with UUID "%s" is not available', $productUuid));
        }

        $this->cartFacade->addProductToExistingCart($product, $quantity, $cart, $isAbsoluteQuantity);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     * @param string|null $cartUuid
     * @return \App\Model\Cart\Cart
     */
    public function getCart(?CustomerUser $customerUser, ?string $cartUuid): Cart
    {
        if ($customerUser === null && $cartUuid !== null) {
            $cart = $this->cartFacade->findCartByCartIdentifier($cartUuid);
            if ($cart === null) {
                throw new UserError(sprintf('Cart "%s" is unavailable.', $cartUuid));
            }

            return $cart;
        }

        if ($customerUser !== null) {
            $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);
        } else {
            $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCartIdentifier($cartUuid);
        }

        return $this->cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists($customerUserIdentifier);
    }
}
