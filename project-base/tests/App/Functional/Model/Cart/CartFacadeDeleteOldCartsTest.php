<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\Model\Product\Product;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartFactory;
use Shopsys\FrameworkBundle\Model\Cart\CartRepository;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Tests\App\Test\TransactionFunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class CartFacadeDeleteOldCartsTest extends TransactionFunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartFactory
     * @inject
     */
    private CartFactory $cartFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     * @inject
     */
    private ProductRepository $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     * @inject
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade
     * @inject
     */
    private CurrentPromoCodeFacade $currentPromoCodeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser
     * @inject
     */
    private ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface
     * @inject
     */
    private CartItemFactoryInterface $cartItemFactoryInterface;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartRepository
     * @inject
     */
    private CartRepository $cartRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade
     * @inject
     */
    private CartWatcherFacade $cartWatcherFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade
     * @inject
     */
    private CustomerUserFacade $customerUserFacade;

    public function testOldUnregisteredCustomerCartGetsDeleted(): void
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForUnregisteredCustomer();
        $cartFacade = $this->getCartFacadeForUnregisteredCustomer();
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 61 days'));

        $this->em->flush();

        $cartFacade->deleteOldCarts();

        $this->assertCartIsDeleted($cartFacade, $customerUserIdentifier, 'Cart should be deleted');
    }

    public function testUnregisteredCustomerCartDoesNotGetDeleted(): void
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForUnregisteredCustomer();
        $cartFacade = $this->getCartFacadeForUnregisteredCustomer();
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 59 days'));

        $this->em->flush();

        $cartFacade->deleteOldCarts();

        $this->assertCartIsNotDeleted($cartFacade, $customerUserIdentifier, 'Cart should not be deleted');
    }

    public function testOldRegisteredCustomerCartGetsDeleted(): void
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForRegisteredCustomer();
        $cartFacade = $this->getCartFacadeForRegisteredCustomer();
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 121 days'));

        $this->em->flush();

        $cartFacade->deleteOldCarts();

        $this->assertCartIsDeleted($cartFacade, $customerUserIdentifier, 'Cart should be deleted');
    }

    public function testRegisteredCustomerCartDoesNotGetDeletedIfItContainsRecentlyAddedItem(): void
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForRegisteredCustomer();
        $cartFacade = $this->getCartFacadeForRegisteredCustomer();
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 119 days'));

        $this->em->flush();

        $cartFacade->deleteOldCarts();

        $this->assertCartIsNotDeleted($cartFacade, $customerUserIdentifier, 'Cart should not be deleted');
    }

    /**
     * @param int $productId
     * @return \App\Model\Product\Product
     */
    private function getProductById(int $productId): Product
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->productFacade->getById($productId);

        return $product;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    private function getCartFacadeForRegisteredCustomer(): CartFacade
    {
        return $this->getCartFacadeForCustomerUser($this->getCustomerUserIdentifierForRegisteredCustomer());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    private function getCartFacadeForUnregisteredCustomer(): CartFacade
    {
        return $this->getCartFacadeForCustomerUser($this->getCustomerUserIdentifierForUnregisteredCustomer());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    private function getCartFacadeForCustomerUser(CustomerUserIdentifier $customerUserIdentifier): CartFacade
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
     * @return \PHPUnit\Framework\MockObject\MockObject|\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory
     */
    private function getCustomerUserIdentifierFactoryMock(CustomerUserIdentifier $customerUserIdentifier): MockObject|CustomerUserIdentifierFactory
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
    private function assertCartIsDeleted(CartFacade $cartFacade, CustomerUserIdentifier $customerUserIdentifier, string $message): void
    {
        $cart = $cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
        $this->assertNull($cart, $message);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @param string $message
     */
    private function assertCartIsNotDeleted(CartFacade $cartFacade, CustomerUserIdentifier $customerUserIdentifier, string $message): void
    {
        $cart = $cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
        $this->assertNotNull($cart, $message);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier
     */
    private function getCustomerUserIdentifierForRegisteredCustomer(): CustomerUserIdentifier
    {
        $customerUser = $this->customerUserFacade->getCustomerUserById(1);

        return new CustomerUserIdentifier('', $customerUser);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier
     */
    private function getCustomerUserIdentifierForUnregisteredCustomer(): CustomerUserIdentifier
    {
        return new CustomerUserIdentifier('randomString');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    private function createCartWithProduct(CustomerUserIdentifier $customerUserIdentifier, CartFacade $cartFacade): Cart
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
