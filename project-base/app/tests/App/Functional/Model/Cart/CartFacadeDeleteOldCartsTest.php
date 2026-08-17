<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\Model\Cart\CartFacade;
use App\Model\Cart\Item\CartItem;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Symfony\Component\Clock\DatePoint;
use Tests\App\Test\TransactionFunctionalTestCase;

class CartFacadeDeleteOldCartsTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private CartFacade $cartFacade;

    /**
     * @inject
     */
    private CustomerUserFacade $customerUserFacade;

    public function testOldUnregisteredCustomerCartGetsDeleted(): void
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForUnregisteredCustomer();
        $cartFacade = $this->cartFacade;
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt((new DatePoint())->modify('- 131 days'));

        $this->em->flush();

        $cartFacade->deleteOldCarts();

        $this->assertCartIsDeleted($cartFacade, $customerUserIdentifier, 'Cart should be deleted');
    }

    public function testUnregisteredCustomerCartDoesNotGetDeleted(): void
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForUnregisteredCustomer();
        $cartFacade = $this->cartFacade;
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt((new DatePoint())->modify('- 129 days'));

        $this->em->flush();

        $cartFacade->deleteOldCarts();

        $this->assertCartIsNotDeleted($cartFacade, $customerUserIdentifier, 'Cart should not be deleted');
    }

    public function testOldRegisteredCustomerCartGetsDeleted(): void
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForRegisteredCustomer();
        $cartFacade = $this->cartFacade;
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt((new DatePoint())->modify('- 131 days'));

        $this->em->flush();

        $cartFacade->deleteOldCarts();

        $this->assertCartIsDeleted($cartFacade, $customerUserIdentifier, 'Cart should be deleted');
    }

    public function testRegisteredCustomerCartDoesNotGetDeletedIfItContainsRecentlyAddedItem(): void
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForRegisteredCustomer();
        $cartFacade = $this->cartFacade;
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt((new DatePoint())->modify('- 129 days'));

        $this->em->flush();

        $cartFacade->deleteOldCarts();

        $this->assertCartIsNotDeleted($cartFacade, $customerUserIdentifier, 'Cart should not be deleted');
    }

    private function getProductById(int $productId): Product
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->productFacade->getById($productId);

        return $product;
    }

    private function assertCartIsDeleted(
        CartFacade $cartFacade,
        CustomerUserIdentifier $customerUserIdentifier,
        string $message,
    ): void {
        $cart = $cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
        $this->assertNull($cart, $message);
    }

    private function assertCartIsNotDeleted(
        CartFacade $cartFacade,
        CustomerUserIdentifier $customerUserIdentifier,
        string $message,
    ): void {
        $cart = $cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
        $this->assertNotNull($cart, $message);
    }

    private function getCustomerUserIdentifierForRegisteredCustomer(): CustomerUserIdentifier
    {
        $customerUser = $this->customerUserFacade->getCustomerUserById(1);

        return new CustomerUserIdentifier('', $customerUser);
    }

    private function getCustomerUserIdentifierForUnregisteredCustomer(): CustomerUserIdentifier
    {
        return new CustomerUserIdentifier('randomString');
    }

    private function createCartWithProduct(
        CustomerUserIdentifier $customerUserIdentifier,
        CartFacade $cartFacade,
    ): Cart {
        $product = $this->getProductById(1);
        $cart = $cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists($customerUserIdentifier);

        $cartItem = new CartItem($cart, $product, 1, Money::zero());

        $this->em->persist($cartItem);
        $this->em->flush();

        $cart->addItem($cartItem);

        return $cart;
    }
}
