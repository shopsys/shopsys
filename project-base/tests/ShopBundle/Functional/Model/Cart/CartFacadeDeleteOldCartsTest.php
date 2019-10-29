<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Model\Cart;

use DateTime;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifierFactory;
use Tests\ShopBundle\Test\TransactionFunctionalTestCase;

class CartFacadeDeleteOldCartsTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartFactory
     * @inject
     */
    private $cartFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     * @inject
     */
    private $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer
     * @inject
     */
    private $currentCustomer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade
     * @inject
     */
    private $currentPromoCodeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser
     * @inject
     */
    private $productPriceCalculationForUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface
     * @inject
     */
    private $cartItemFactoryInterface;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartRepository
     * @inject
     */
    private $cartRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade
     * @inject
     */
    private $cartWatcherFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     * @inject
     */
    private $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade
     * @inject
     */
    private $customerFacade;

    public function testOldUnregisteredCustomerCartGetsDeleted()
    {
        $customerIdentifier = $this->getCustomerIdentifierForUnregisteredCustomer();
        $cartFacade = $this->getCartFacadeForUnregisteredCustomer();
        $cart = $this->createCartWithProduct($customerIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 61 days'));

        $em = $this->getEntityManager();
        $em->flush($cart);

        $cartFacade->deleteOldCarts();

        $this->assertCartIsDeleted($cartFacade, $customerIdentifier, 'Cart should be deleted');
    }

    public function testUnregisteredCustomerCartDoesNotGetDeleted()
    {
        $customerIdentifier = $this->getCustomerIdentifierForUnregisteredCustomer();
        $cartFacade = $this->getCartFacadeForUnregisteredCustomer();
        $cart = $this->createCartWithProduct($customerIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 59 days'));

        $em = $this->getEntityManager();
        $em->flush($cart);

        $cartFacade->deleteOldCarts();

        $this->assertCartIsNotDeleted($cartFacade, $customerIdentifier, 'Cart should not be deleted');
    }

    public function testOldRegisteredCustomerCartGetsDeleted()
    {
        $customerIdentifier = $this->getCustomerIdentifierForRegisteredCustomer();
        $cartFacade = $this->getCartFacadeForRegisteredCustomer();
        $cart = $this->createCartWithProduct($customerIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 121 days'));

        $em = $this->getEntityManager();
        $em->flush($cart);

        $cartFacade->deleteOldCarts();

        $this->assertCartIsDeleted($cartFacade, $customerIdentifier, 'Cart should be deleted');
    }

    public function testRegisteredCustomerCartDoesNotGetDeletedIfItContainsRecentlyAddedItem()
    {
        $customerIdentifier = $this->getCustomerIdentifierForRegisteredCustomer();
        $cartFacade = $this->getCartFacadeForRegisteredCustomer();
        $cart = $this->createCartWithProduct($customerIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 119 days'));

        $em = $this->getEntityManager();
        $em->flush($cart);

        $cartFacade->deleteOldCarts();

        $this->assertCartIsNotDeleted($cartFacade, $customerIdentifier, 'Cart should not be deleted');
    }

    /**
     * @param int $productId
     * @return \Shopsys\ShopBundle\Model\Product\Product
     */
    private function getProductById($productId)
    {
        /** @var \Shopsys\ShopBundle\Model\Product\Product $product */
        $product = $this->productFacade->getById($productId);

        return $product;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    private function getCartFacadeForRegisteredCustomer()
    {
        $user = $this->customerFacade->getUserById(1);

        return $this->getCartFacadeForCustomer($this->getCustomerIdentifierForRegisteredCustomer());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    private function getCartFacadeForUnregisteredCustomer()
    {
        return $this->getCartFacadeForCustomer($this->getCustomerIdentifierForUnregisteredCustomer());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier $customerIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    private function getCartFacadeForCustomer(CustomerIdentifier $customerIdentifier)
    {
        return new CartFacade(
            $this->getEntityManager(),
            $this->cartFactory,
            $this->productRepository,
            $this->getCustomerIdentifierFactoryMock($customerIdentifier),
            $this->domain,
            $this->currentCustomer,
            $this->currentPromoCodeFacade,
            $this->productPriceCalculationForUser,
            $this->cartItemFactoryInterface,
            $this->cartRepository,
            $this->cartWatcherFacade
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier $customerIdentifier
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getCustomerIdentifierFactoryMock(CustomerIdentifier $customerIdentifier)
    {
        $customerIdentifierFactoryMock = $this->getMockBuilder(CustomerIdentifierFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerIdentifierFactoryMock->method('get')->willReturn($customerIdentifier);

        return $customerIdentifierFactoryMock;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier $customerIdentifier
     * @param string $message
     */
    private function assertCartIsDeleted(CartFacade $cartFacade, CustomerIdentifier $customerIdentifier, $message)
    {
        $cart = $cartFacade->findCartByCustomerIdentifier($customerIdentifier);
        $this->assertNull($cart, $message);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier $customerIdentifier
     * @param string $message
     */
    private function assertCartIsNotDeleted(CartFacade $cartFacade, CustomerIdentifier $customerIdentifier, $message)
    {
        $cart = $cartFacade->findCartByCustomerIdentifier($customerIdentifier);
        $this->assertNotNull($cart, $message);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier
     */
    private function getCustomerIdentifierForRegisteredCustomer()
    {
        $user = $this->customerFacade->getUserById(1);

        return new CustomerIdentifier('', $user);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier
     */
    private function getCustomerIdentifierForUnregisteredCustomer()
    {
        return new CustomerIdentifier('randomString');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier $customerIdentifier
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    private function createCartWithProduct(CustomerIdentifier $customerIdentifier, CartFacade $cartFacade)
    {
        $em = $this->getEntityManager();

        $product = $this->getProductById(1);
        $cart = $cartFacade->getCartByCustomerIdentifierCreateIfNotExists($customerIdentifier);

        $cartItem = new CartItem($cart, $product, 1, Money::zero());

        $em->persist($cartItem);
        $em->flush();

        $cart->addItem($cartItem);

        return $cart;
    }
}
