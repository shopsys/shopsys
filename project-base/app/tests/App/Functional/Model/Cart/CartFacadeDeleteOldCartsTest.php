<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\Model\Cart\CartFacade;
use App\Model\Customer\User\CurrentCustomerUser;
use App\Model\Order\Item\OrderItem;
use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use App\Model\Product\ProductRepository;
use DateTime;
use Shopsys\FrameworkBundle\Model\Cart\CartFactory;
use Shopsys\FrameworkBundle\Model\Cart\CartRepository;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class CartFacadeDeleteOldCartsTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private CartFactory $cartFactory;

    /**
     * @inject
     */
    private ProductRepository $productRepository;

    /**
     * @inject
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @inject
     */
    private CurrentPromoCodeFacade $currentPromoCodeFacade;

    /**
     * @inject
     */
    private ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser;

    /**
     * @inject
     */
    private CartItemFactory $cartItemFactory;

    /**
     * @inject
     */
    private CartRepository $cartRepository;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @inject
     */
    private ProductAvailabilityFacade $productAvailabilityFacade;

    /**
     * @inject
     */
    private OrderDataFactory $orderDataFactory;

    /**
     * @inject
     */
    private OrderItemFactory $orderItemFactory;

    /**
     * @inject
     */
    private OrderItemDataFactory $orderItemDataFactory;

    public function testOldUnregisteredCustomerCartGetsDeleted()
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForUnregisteredCustomer();
        $cartFacade = $this->getCartFacadeForUnregisteredCustomer();
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 131 days'));

        $this->em->flush();

        $cartFacade->deleteOldCarts();

        $this->assertCartIsDeleted($cartFacade, $customerUserIdentifier, 'Cart should be deleted');
    }

    public function testUnregisteredCustomerCartDoesNotGetDeleted()
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForUnregisteredCustomer();
        $cartFacade = $this->getCartFacadeForUnregisteredCustomer();
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 129 days'));

        $this->em->flush();

        $cartFacade->deleteOldCarts();

        $this->assertCartIsNotDeleted($cartFacade, $customerUserIdentifier, 'Cart should not be deleted');
    }

    public function testOldRegisteredCustomerCartGetsDeleted()
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForRegisteredCustomer();
        $cartFacade = $this->getCartFacadeForRegisteredCustomer();
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 131 days'));

        $this->em->flush();

        $cartFacade->deleteOldCarts();

        $this->assertCartIsDeleted($cartFacade, $customerUserIdentifier, 'Cart should be deleted');
    }

    public function testRegisteredCustomerCartDoesNotGetDeletedIfItContainsRecentlyAddedItem()
    {
        $customerUserIdentifier = $this->getCustomerUserIdentifierForRegisteredCustomer();
        $cartFacade = $this->getCartFacadeForRegisteredCustomer();
        $cart = $this->createCartWithProduct($customerUserIdentifier, $cartFacade);

        $cart->setModifiedAt(new DateTime('- 129 days'));

        $this->em->flush();

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
     * @return \App\Model\Cart\CartFacade
     */
    private function getCartFacadeForRegisteredCustomer()
    {
        return $this->getCartFacadeForCustomerUser($this->getCustomerUserIdentifierForRegisteredCustomer());
    }

    /**
     * @return \App\Model\Cart\CartFacade
     */
    private function getCartFacadeForUnregisteredCustomer()
    {
        return $this->getCartFacadeForCustomerUser($this->getCustomerUserIdentifierForUnregisteredCustomer());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @return \App\Model\Cart\CartFacade
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
            $this->cartItemFactory,
            $this->cartRepository,
            $this->productAvailabilityFacade,
            $this->orderDataFactory,
            $this->orderItemFactory,
            $this->orderItemDataFactory,
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @return \PHPUnit\Framework\MockObject\MockObject|\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory
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
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @param string $message
     */
    private function assertCartIsDeleted(
        CartFacade $cartFacade,
        CustomerUserIdentifier $customerUserIdentifier,
        $message,
    ) {
        $cart = $cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
        $this->assertNull($cart, $message);
    }

    /**
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @param string $message
     */
    private function assertCartIsNotDeleted(
        CartFacade $cartFacade,
        CustomerUserIdentifier $customerUserIdentifier,
        $message,
    ) {
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
        return new CustomerUserIdentifier('c706442b-1f1a-4c42-8411-ea8d2c784b81');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier
     * @param \App\Model\Cart\CartFacade $cartFacade
     * @return \App\Model\Order\Order
     */
    private function createCartWithProduct(CustomerUserIdentifier $customerUserIdentifier, CartFacade $cartFacade)
    {
        $product = $this->getProductById(1);
        $cart = $cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists($customerUserIdentifier);

        $cartItem = new OrderItem(
            $cart,
            $product->getName('cs'),
            Price::zero(),
            '0',
            1,
            OrderItemTypeEnum::TYPE_PRODUCT,
            null,
            null,
        );

        $this->em->persist($cartItem);
        $this->em->flush();

        $cart->addItem($cartItem);

        return $cart;
    }
}
