<?php

namespace Tests\ShopBundle\Database\Model\Cart;

use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFactory;
use Shopsys\FrameworkBundle\Model\Cart\CartService;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemRepository;
use Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Tests\ShopBundle\Test\DatabaseTestCase;

class CartFacadeDeleteOldCartsTest extends DatabaseTestCase
{
    public function testOldUnregisteredCustomerCartGetsDeleted(): void
    {
        $product = $this->getProductById(1);

        $cartFacade = $this->getCartFacadeForUnregisteredCustomer();
        $this->addProductToCartAtTime($cartFacade, $product, new DateTime('- 61 days'));

        $cartFacade->deleteOldCarts();

        $this->assertCartItemCount($cartFacade, 0, 'Cart items should be deleted');
    }

    public function testUnregisteredCustomerCartDoesNotGetDeletedIfItContainsRecentlyAddedItem(): void
    {
        $product1 = $this->getProductById(1);
        $product2 = $this->getProductById(2);

        $cartFacade = $this->getCartFacadeForUnregisteredCustomer();
        $this->addProductToCartAtTime($cartFacade, $product1, new DateTime('- 59 days'));
        $this->addProductToCartAtTime($cartFacade, $product2, new DateTime('- 61 days'));

        $cartFacade->deleteOldCarts();

        $this->assertCartItemCount($cartFacade, 2, 'Cart items should not be deleted');
    }

    public function testOldRegisteredCustomerCartGetsDeleted(): void
    {
        $product = $this->getProductById(1);

        $cartFacade = $this->getCartFacadeForRegisteredCustomer();
        $this->addProductToCartAtTime($cartFacade, $product, new DateTime('- 121 days'));

        $cartFacade->deleteOldCarts();

        $this->assertCartItemCount($cartFacade, 0, 'Cart items should be deleted');
    }

    public function testRegisteredCustomerCartDoesNotGetDeletedIfItContainsRecentlyAddedItem(): void
    {
        $product1 = $this->getProductById(1);
        $product2 = $this->getProductById(2);

        $cartFacade = $this->getCartFacadeForRegisteredCustomer();
        $this->addProductToCartAtTime($cartFacade, $product1, new DateTime('- 119 days'));
        $this->addProductToCartAtTime($cartFacade, $product2, new DateTime('- 121 days'));

        $cartFacade->deleteOldCarts();

        $this->assertCartItemCount($cartFacade, 2, 'Cart items should not be deleted');
    }
    
    private function getProductById(int $productId): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        $productFacade = $this->getContainer()->get(ProductFacade::class);
        /* @var $productFacade \Shopsys\FrameworkBundle\Model\Product\ProductFacade */

        return $productFacade->getById($productId);
    }

    private function getCartFacadeForRegisteredCustomer(): \Shopsys\FrameworkBundle\Model\Cart\CartFacade
    {
        $customerFacade = $this->getContainer()->get(CustomerFacade::class);
        /* @var $customerFacade \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade */

        $user = $customerFacade->getUserById(1);

        return $this->getCartFacadeForCustomer(new CustomerIdentifier('', $user));
    }

    private function getCartFacadeForUnregisteredCustomer(): \Shopsys\FrameworkBundle\Model\Cart\CartFacade
    {
        return $this->getCartFacadeForCustomer(new CustomerIdentifier('randomString'));
    }

    private function getCartFacadeForCustomer(CustomerIdentifier $customerIdentifier): \Shopsys\FrameworkBundle\Model\Cart\CartFacade
    {
        return new CartFacade(
            $this->getEntityManager(),
            $this->getContainer()->get(CartService::class),
            $this->getContainer()->get(CartFactory::class),
            $this->getContainer()->get(ProductRepository::class),
            $this->getCustomerIdentifierFactoryMock($customerIdentifier),
            $this->getContainer()->get(Domain::class),
            $this->getContainer()->get(CurrentCustomer::class),
            $this->getContainer()->get(CurrentPromoCodeFacade::class),
            $this->getContainer()->get(CartItemRepository::class)
        );
    }

    private function getCustomerIdentifierFactoryMock(CustomerIdentifier $customerIdentifier): \PHPUnit\Framework\MockObject\MockObject
    {
        $customerIdentifierFactoryMock = $this->getMockBuilder(CustomerIdentifierFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerIdentifierFactoryMock->method('get')->willReturn($customerIdentifier);

        return $customerIdentifierFactoryMock;
    }

    private function addProductToCartAtTime(CartFacade $cartFacade, Product $product, DateTime $addedAt): void
    {
        $cartItemResult = $cartFacade->addProductToCart($product->getId(), 1);

        $cartItemResult->getCartItem()->changeAddedAt($addedAt);

        $this->getEntityManager()->flush($cartItemResult->getCartItem());
    }
    
    private function assertCartItemCount(CartFacade $cartFacade, int $count, string $message): void
    {
        $cartItems = $cartFacade->getCartOfCurrentCustomer()->getItems();
        $this->assertCount($count, $cartItems, $message);
    }
}
