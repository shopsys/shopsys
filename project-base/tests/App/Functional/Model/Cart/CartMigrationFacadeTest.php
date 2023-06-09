<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Doctrine\ORM\EntityManager;
use ReflectionClass;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartMigrationFacade;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class CartMigrationFacadeTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @inject
     */
    private CartItemFactoryInterface $cartItemFactory;

    public function testMergeWithCartReturnsCartWithSummedProducts()
    {
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2);

        // Cart merging is bound to Product Id
        $productReflectionClass = new ReflectionClass(Product::class);
        $idProperty = $productReflectionClass->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($product1, 1);
        $idProperty->setValue($product2, 2);

        $cartIdentifier1 = 'abc123';
        $cartIdentifier2 = 'def456';

        $customerUserIdentifier1 = new CustomerUserIdentifier($cartIdentifier1);
        $mainCart = new Cart($customerUserIdentifier1->getCartIdentifier());

        $customerUserIdentifier2 = new CustomerUserIdentifier($cartIdentifier2);
        $mergingCart = new Cart($customerUserIdentifier2->getCartIdentifier());

        $cartItem = new CartItem($mainCart, $product1, 2, Money::zero());
        $mainCart->addItem($cartItem);

        $cartItem1 = new CartItem($mergingCart, $product1, 3, Money::zero());
        $mergingCart->addItem($cartItem1);
        $cartItem2 = new CartItem($mergingCart, $product2, 1, Money::zero());
        $mergingCart->addItem($cartItem2);

        $entityManagerMock = $this->getMockBuilder(EntityManager::class)
            ->setMethods(['persist', 'flush'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerUserIdentifierFactory = $this->getMockBuilder(CustomerUserIdentifierFactory::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();
        $customerUserIdentifierFactory
            ->expects($this->any())->method('get')
            ->willReturn($customerUserIdentifier1);
        $cartFacadeMock = $this->getMockBuilder(CartFacade::class)
            ->setMethods(['getCartByCustomerUserIdentifierCreateIfNotExists', 'deleteCart'])
            ->disableOriginalConstructor()
            ->getMock();
        $cartFacadeMock
            ->expects($this->once())->method('getCartByCustomerUserIdentifierCreateIfNotExists')
            ->willReturn($mainCart);
        $cartFacadeMock
            ->expects($this->once())->method('deleteCart')
            ->with($this->equalTo($mergingCart));

        $cartMigrationFacade = new CartMigrationFacade(
            $entityManagerMock,
            $customerUserIdentifierFactory,
            $this->cartItemFactory,
            $cartFacadeMock
        );

        $cartMigrationFacade->mergeCurrentCartWithCart($mergingCart);

        $this->assertSame(2, $mainCart->getItemsCount());

        $this->assertSame(5, $mainCart->getItems()[0]->getQuantity());
        $this->assertSame(1, $mainCart->getItems()[1]->getQuantity());
    }
}
