<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Cart\CartFacade;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class MergeCartsTest extends GraphQlWithLoginTestCase
{
    /**
     * @inject
     */
    private CartFacade $cartFacade;

    /**
     * @inject
     */
    private CustomerUserIdentifierFactory $customerUserIdentifierFactory;

    /**
     * @inject
     */
    private CurrentCustomerUser $currentCustomerUser;

    /**
     * @inject
     */
    private FrontendCustomerUserProvider $frontendCustomerUserProvider;

    public function testCartIsMergedAfterLogin(): void
    {
        $anonymouslyAddedProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5', Product::class);
        $anonymouslyAddedProductQuantity = 6;
        $this->addProductToCustomerCart($anonymouslyAddedProduct, $anonymouslyAddedProductQuantity);

        $anonymouslyAddedProduct2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $anonymouslyAddedProductQuantity2 = 1;
        $this->addProductToCustomerCart($anonymouslyAddedProduct2, $anonymouslyAddedProductQuantity2);

        $testCartUuid = CartDataFixture::CART_UUID;

        $loginMutationWithCartUuid = 'mutation {
                Login(input: {
                    email: "no-reply@shopsys.com"
                    password: "user123"
                    cartUuid: "' . $testCartUuid . '"
                }) {
                    tokens {
                        accessToken
                        refreshToken
                    }
                    showCartMergeInfo
                }
            }
        ';

        $response = $this->getResponseDataForGraphQlType(
            $this->getResponseContentForQuery($loginMutationWithCartUuid),
            'Login',
        );

        $cart = $this->findCartOfCurrentCustomer();

        self::assertNotNull($cart);

        self::assertTrue($response['showCartMergeInfo']);

        $cartItems = $cart->getItems();
        self::assertCount(3, $cartItems);

        $firstProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        self::assertEquals($firstProduct->getFullname(), $cartItems[0]->getName(), 'Third product name mismatch');
        self::assertEquals(3, $cartItems[0]->getQuantity(), 'Third product quantity mismatch');

        self::assertEquals($anonymouslyAddedProduct->getFullname(), $cartItems[1]->getName(), 'First product name mismatch');
        self::assertEquals($anonymouslyAddedProductQuantity, $cartItems[1]->getQuantity(), 'First product quantity mismatch');

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);
        self::assertEquals($secondProduct->getFullname(), $cartItems[2]->getName(), 'Second product name mismatch');
        self::assertEquals(2, $cartItems[2]->getQuantity(), 'Second product quantity mismatch');

        $oldCart = $this->cartFacade->findCartByCartIdentifier($testCartUuid);
        self::assertNull($oldCart);
    }

    public function testCartIsOverwrittenAfterLogin(): void
    {
        $productAddedToCustomerUserCart = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '5', Product::class);
        $productAddedToCustomerUserCartQuantity = 6;
        $this->addProductToCustomerCart($productAddedToCustomerUserCart, $productAddedToCustomerUserCartQuantity);

        $productAddedToCustomerUserCart2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $productAddedToCustomerUserCart2Quantity = 1;
        $this->addProductToCustomerCart($productAddedToCustomerUserCart2, $productAddedToCustomerUserCart2Quantity);

        $testCartUuid = CartDataFixture::CART_UUID;

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/OverwriteCartAfterLogin.graphql',
            [
                'email' => 'no-reply@shopsys.com',
                'password' => 'user123',
                'cartUuid' => $testCartUuid,
                'shouldOverwriteCustomerUserCart' => true,
            ],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'Login');

        $cart = $this->findCartOfCurrentCustomer();

        self::assertNotNull($cart);

        self::assertFalse($data['showCartMergeInfo']);

        $cartItems = $cart->getItems();
        self::assertCount(2, $cartItems);

        $firstProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);
        self::assertEquals($firstProduct->getFullname(), $cartItems[0]->getName(), 'First product name mismatch');
        self::assertEquals(2, $cartItems[0]->getQuantity(), 'First product quantity mismatch');

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        self::assertEquals($secondProduct->getFullname(), $cartItems[1]->getName(), 'Second product name mismatch');
        self::assertEquals(2, $cartItems[1]->getQuantity(), 'Second product quantity mismatch');

        $oldCart = $this->cartFacade->findCartByCartIdentifier($testCartUuid);
        self::assertNull($oldCart);
    }

    public function testCartIsMergedAfterRegister(): void
    {
        $testCartUuid = CartDataFixture::CART_UUID;

        $registerMutationWithCartUuid = 'mutation {
                    Register(input: {
                        email: "test@example.com"
                        firstName: "Test"
                        lastName: "Test"
                        password: "testTEST123"
                        telephone: "145612314"
                        newsletterSubscription: false
                        street: "123 Fake Street"
                        city: "Springfield"
                        postcode: "12345"
                        companyCustomer: false
                        country: "CZ"
                        cartUuid: "' . CartDataFixture::CART_UUID . '"
                    }) {
                        tokens{
                          accessToken
                          refreshToken
                        }
                        showCartMergeInfo
                    }
                }';

        $response = $this->getResponseDataForGraphQlType(
            $this->getResponseContentForQuery($registerMutationWithCartUuid),
            'Register',
        );

        $cart = $this->findCartOfCustomerByEmail('test@example.com');

        self::assertNotNull($cart);

        self::assertFalse($response['showCartMergeInfo']);

        $cartItems = $cart->getItems();
        self::assertCount(2, $cartItems);

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);
        self::assertEquals($secondProduct->getFullname(), $cartItems[0]->getName(), 'Second product name mismatch');
        self::assertEquals(2, $cartItems[0]->getQuantity(), 'Second product quantity mismatch');

        $firstProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        self::assertEquals($firstProduct->getFullname(), $cartItems[1]->getName(), 'Third product name mismatch');
        self::assertEquals(2, $cartItems[1]->getQuantity(), 'Third product quantity mismatch');

        $oldCart = $this->cartFacade->findCartByCartIdentifier($testCartUuid);
        self::assertNull($oldCart);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart|null
     */
    private function findCartOfCurrentCustomer(): ?Cart
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);

        return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
    }

    /**
     * @param string $email
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart|null
     */
    private function findCartOfCustomerByEmail(string $email): ?Cart
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->frontendCustomerUserProvider->loadUserByUsername($email);

        $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);

        return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $productQuantity
     */
    private function addProductToCustomerCart(Product $product, int $productQuantity): void
    {
        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => $productQuantity,
        ]);
    }
}
