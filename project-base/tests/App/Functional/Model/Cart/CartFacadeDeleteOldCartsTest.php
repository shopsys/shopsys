<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use DateTime;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class CartFacadeDeleteOldCartsTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

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
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     * @inject
     */
    private $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade
     * @inject
     */
    private $currentPromoCodeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser
     * @inject
     */
    private $productPriceCalculationForCustomerUser;

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
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     * @inject
     */
    private $customerUserFacade;

    public function testOldUnregisteredCustomerCartGetsDeleted()
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForUnregisteredCustomer();
        $cartFacade = $this->getCartFacadeForUnregisteredCustomer();
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 61 days'));

        $this->em->flush($cart);

        $cartFacade->deleteOldCarts();

        $this->assertCartIsDeleted($cartFacade, $customerUserIdentifier, 'Cart should be deleted');
    }

    public function testUnregisteredCustomerCartDoesNotGetDeleted()
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForUnregisteredCustomer();
        $cartFacade = $this->getCartFacadeForUnregisteredCustomer();
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 59 days'));

        $this->em->flush($cart);

        $cartFacade->deleteOldCarts();

        $this->assertCartIsNotDeleted($cartFacade, $customerUserIdentifier, 'Cart should not be deleted');
    }

    public function testOldRegisteredCustomerCartGetsDeleted()
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForRegisteredCustomer();
        $cartFacade = $this->getCartFacadeForRegisteredCustomer();
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 121 days'));

        $this->em->flush($cart);

        $cartFacade->deleteOldCarts();

        $this->assertCartIsDeleted($cartFacade, $customerUserIdentifier, 'Cart should be deleted');
    }

    public function testRegisteredCustomerCartDoesNotGetDeletedIfItContainsRecentlyAddedItem()
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForRegisteredCustomer();
        $cartFacade = $this->getCartFacadeForRegisteredCustomer();
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 119 days'));

        $this->em->flush($cart);

        $cartFacade->deleteOldCarts();

        $this->assertCartIsNotDeleted($cartFacade, $customerUserIdentifier, 'Cart should not be deleted');
    }

    /**
     * @param int $productId
     * @return \App\Model\Product\Product
     */
    private function getProductById($productId)
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->productFacade->getById($productId);

        return $product;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    private function getCartFacadeForRegisteredCustomer()
    {
        return $this->getCartFacadeForCustomerUser($this->getCustomerUserIdentifierForRegisteredCustomer());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    private function getCartFacadeForUnregisteredCustomer()
    {
        return $this->getCartFacadeForCustomerUser($this->getCustomerUserIdentifierForUnregisteredCustomer());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     *
     * @return \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    private function getCartFacadeForCustomerUser(CustomerUserIdentifier $customerUserIdentifier)
    {
        return new CartFacade(
            $this->em,
            $this->cartFactory,
            $this->productRepository,
            $this->getCustomerUserIdentifierFactoryMock($customerUserIdentifier),
            $this->domain,
            $this->currentCustomerUser,
            $this->currentPromoCodeFacade,
            $this->productPriceCalculationForCustomerUser,
            $this->cartItemFactoryInterface,
            $this->cartRepository,
            $this->cartWatcherFacade
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     *
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getCustomerUserIdentifierFactoryMock(CustomerUserIdentifier $customerUserIdentifier)
    {
        $customerUserIdentifierFactoryMock = $this->getMockBuilder(CustomerUserIdentifierFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerUserIdentifierFactoryMock->method('get')->willReturn($customerUserIdentifier);

        return $customerUserIdentifierFactoryMock;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @param string $message
     */
    private function assertCartIsDeleted(CartFacade $cartFacade, CustomerUserIdentifier $customerUserIdentifier, $message)
    {
        $cart = $cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
        $this->assertNull($cart, $message);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @param string $message
     */
    private function assertCartIsNotDeleted(CartFacade $cartFacade, CustomerUserIdentifier $customerUserIdentifier, $message)
    {
        $cart = $cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
        $this->assertNotNull($cart, $message);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier
     */
    private function getCustomerUserIdentifierForRegisteredCustomer()
    {
        $customerUser = $this->customerUserFacade->getCustomerUserById(1);

        return new CustomerUserIdentifier('', $customerUser);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier
     */
    private function getCustomerUserIdentifierForUnregisteredCustomer()
    {
        return new CustomerUserIdentifier('randomString');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     *
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    private function createCartWithProduct(CustomerUserIdentifier $customerUserIdentifier, CartFacade $cartFacade)
    {
        $product = $this->getProductById(1);
        $cart = $cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists($customerUserIdentifier);

        $cartItem = new CartItem($cart, $product, 1, Money::zero());

        $this->em->persist($cartItem);
        $this->em->flush();

        $cart->addItem($cartItem);

        return $cart;
    }
}
