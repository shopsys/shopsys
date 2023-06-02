<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Cart\Cart;
use App\Model\Cart\CartFacade;
use App\Model\Customer\User\CustomerUserIdentifierFactory;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class AuthenticatedAddToCartTest extends GraphQlWithLoginTestCase
{
    /**
     * @var \App\Model\Cart\CartFacade
     * @inject
     */
    private CartFacade $cartFacade;

    /**
     * @var \App\Model\Customer\User\CustomerUserIdentifierFactory
     * @inject
     */
    private CustomerUserIdentifierFactory $customerUserIdentifierFactory;

    /**
     * @var \App\Model\Product\Product
     */
    private Product $testingProduct;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     * @inject
     */
    private CurrentCustomerUser $currentCustomerUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
    }

    public function testProductIsAddedToCustomerCart(): void
    {
        $productQuantity = 6;
        $newlyCreatedCart = $this->addTestingProductToCustomerCart($productQuantity);

        self::assertEquals('', $newlyCreatedCart['uuid']);

        $cart = $this->findCartOfCurrentCustomer();

        self::assertNotNull($cart);

        $cartItems = $cart->getItems();

        self::assertCount(1, $cartItems);
        self::assertEquals($productQuantity, $cartItems[0]->getQuantity());
    }

    public function testProductIsAddedToExistingCart(): void
    {
        $initialProductQuantity = 6;
        $this->addTestingProductToCustomerCart($initialProductQuantity);

        $addedProductQuantity = 3;

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => $addedProductQuantity,
        ]);

        $cart = $this->findCartOfCurrentCustomer();
        self::assertNotNull($cart);

        $cartItems = $cart->getItems();
        self::assertCount(1, $cartItems);
        self::assertEquals($initialProductQuantity + $addedProductQuantity, $cartItems[0]->getQuantity());
    }

    public function testAnotherProductIsAddedToCart(): void
    {
        $productQuantity = 2;
        $this->addTestingProductToCustomerCart($productQuantity);

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72);
        $secondProductQuantity = 5;

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $secondProduct->getUuid(),
            'quantity' => $secondProductQuantity,
        ]);

        $cart = $this->findCartOfCurrentCustomer();
        self::assertNotNull($cart);

        $cartItems = $cart->getItems();
        self::assertCount(2, $cartItems);

        self::assertEquals($productQuantity, $cartItems[0]->getQuantity());
        self::assertEquals($this->testingProduct->getUuid(), $cartItems[0]->getProduct()->getUuid());

        self::assertEquals($secondProductQuantity, $cartItems[1]->getQuantity());
        self::assertEquals($secondProduct->getUuid(), $cartItems[1]->getProduct()->getUuid());
    }

    public function testProductQuantityIsChangedInExistingCart(): void
    {
        $initialProductQuantity = 2;
        $this->addTestingProductToCustomerCart($initialProductQuantity);

        $desiredProductQuantity = 3;

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => $desiredProductQuantity,
            'isAbsoluteQuantity' => true,
        ]);

        $cart = $this->findCartOfCurrentCustomer();
        self::assertNotNull($cart);

        $cartItems = $cart->getItems();
        self::assertCount(1, $cartItems);
        self::assertEquals($desiredProductQuantity, $cartItems[0]->getQuantity());
    }

    /**
     * @param int $productQuantity
     * @return array
     */
    private function addTestingProductToCustomerCart(int $productQuantity): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => $productQuantity,
        ]);

        return $response['data']['AddToCart']['cart'];
    }

    /**
     * @return \App\Model\Cart\Cart|null
     */
    private function findCartOfCurrentCustomer(): ?Cart
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);

        return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
    }
}
