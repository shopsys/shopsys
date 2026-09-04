<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\AdditionalServiceDataFixture;
use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Cart\CartFacade;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\FrontendCustomerUserProvider;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
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

        self::assertEquals($anonymouslyAddedProduct->getFullName(), $cartItems[0]->getName(), 'First product name mismatch');
        self::assertEquals($anonymouslyAddedProductQuantity, $cartItems[0]->getQuantity(), 'First product quantity mismatch');

        self::assertEquals($anonymouslyAddedProduct2->getFullName(), $cartItems[1]->getName(), 'Second product name mismatch');
        self::assertEquals(3, $cartItems[1]->getQuantity(), 'Second product quantity mismatch');

        $thirdProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);
        self::assertEquals($thirdProduct->getFullName(), $cartItems[2]->getName(), 'Third product name mismatch');
        self::assertEquals(2, $cartItems[2]->getQuantity(), 'Third product quantity mismatch');

        $oldCart = $this->cartFacade->findCartByCartIdentifier($testCartUuid);
        self::assertNull($oldCart);
    }

    public function testAdditionalServicesAreMergedAfterLogin(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $this->addProductToCustomerCart($product, 1);

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $assemblyService = $this->getReference(AdditionalServiceDataFixture::ADDITIONAL_SERVICE_ASSEMBLY, AdditionalService::class);
        $giftWrappingService = $this->getReference(AdditionalServiceDataFixture::ADDITIONAL_SERVICE_GIFT_WRAPPING, AdditionalService::class);
        $engravingService = $this->getReference(AdditionalServiceDataFixture::ADDITIONAL_SERVICE_ENGRAVING, AdditionalService::class);

        /** @var \App\Model\Cart\Cart $customerCart */
        $customerCart = $this->findCartOfCustomerByEmail('no-reply@shopsys.com');
        $this->cartFacade->setItemAdditionalServicesInExistingCartByUuid(
            $this->getCartItemByProduct($customerCart, $product)->getUuid(),
            [$assemblyService, $giftWrappingService],
            $customerCart,
        );

        /** @var \App\Model\Cart\Cart $anonymousCart */
        $anonymousCart = $this->cartFacade->findCartByCartIdentifier(CartDataFixture::CART_UUID);
        $this->cartFacade->setItemAdditionalServicesInExistingCartByUuid(
            $this->getCartItemByProduct($anonymousCart, $product)->getUuid(),
            [$giftWrappingService, $engravingService],
            $anonymousCart,
        );

        $loginMutationWithCartUuid = 'mutation {
                Login(input: {
                    email: "no-reply@shopsys.com"
                    password: "user123"
                    cartUuid: "' . CartDataFixture::CART_UUID . '"
                }) {
                    tokens {
                        accessToken
                    }
                }
            }
        ';
        $this->getResponseDataForGraphQlType(
            $this->getResponseContentForQuery($loginMutationWithCartUuid),
            'Login',
        );

        $mergedCart = $this->findCartOfCurrentCustomer();
        self::assertNotNull($mergedCart);

        $mergedCartItem = $this->getCartItemByProduct($mergedCart, $product);
        $mergedAdditionalServiceIds = array_map(
            static fn (AdditionalService $additionalService): int => $additionalService->getId(),
            $mergedCartItem->getAdditionalServices(),
        );

        self::assertEqualsCanonicalizing(
            [$assemblyService->getId(), $giftWrappingService->getId(), $engravingService->getId()],
            $mergedAdditionalServiceIds,
            'The merged cart item is expected to keep the union of the additional services chosen in both carts',
        );
    }

    public function testAdditionalServicesAreKeptFromBothCartsWhenCartIsOverwrittenAfterLogin(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $this->addProductToCustomerCart($product, 1);

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $assemblyService = $this->getReference(AdditionalServiceDataFixture::ADDITIONAL_SERVICE_ASSEMBLY, AdditionalService::class);
        $giftWrappingService = $this->getReference(AdditionalServiceDataFixture::ADDITIONAL_SERVICE_GIFT_WRAPPING, AdditionalService::class);
        $engravingService = $this->getReference(AdditionalServiceDataFixture::ADDITIONAL_SERVICE_ENGRAVING, AdditionalService::class);

        /** @var \App\Model\Cart\Cart $customerCart */
        $customerCart = $this->findCartOfCustomerByEmail('no-reply@shopsys.com');
        $this->cartFacade->setItemAdditionalServicesInExistingCartByUuid(
            $this->getCartItemByProduct($customerCart, $product)->getUuid(),
            [$assemblyService, $giftWrappingService],
            $customerCart,
        );

        /** @var \App\Model\Cart\Cart $anonymousCart */
        $anonymousCart = $this->cartFacade->findCartByCartIdentifier(CartDataFixture::CART_UUID);
        $this->cartFacade->setItemAdditionalServicesInExistingCartByUuid(
            $this->getCartItemByProduct($anonymousCart, $product)->getUuid(),
            [$giftWrappingService, $engravingService],
            $anonymousCart,
        );

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/OverwriteCartAfterLogin.graphql',
            [
                'email' => 'no-reply@shopsys.com',
                'password' => 'user123',
                'cartUuid' => CartDataFixture::CART_UUID,
                'shouldOverwriteCustomerUserCart' => true,
            ],
        );
        $this->getResponseDataForGraphQlType($response, 'Login');

        $overwrittenCart = $this->findCartOfCurrentCustomer();
        self::assertNotNull($overwrittenCart);

        $keptCartItem = $this->getCartItemByProduct($overwrittenCart, $product);
        $keptAdditionalServiceIds = array_map(
            static fn (AdditionalService $additionalService): int => $additionalService->getId(),
            $keptCartItem->getAdditionalServices(),
        );

        self::assertEqualsCanonicalizing(
            [$assemblyService->getId(), $giftWrappingService->getId(), $engravingService->getId()],
            $keptAdditionalServiceIds,
            'The kept cart item is expected to carry the union of the additional services chosen in both carts',
        );
        self::assertEqualsCanonicalizing(
            [$assemblyService->getId(), $giftWrappingService->getId(), $engravingService->getId()],
            array_keys($keptCartItem->getWatchedAdditionalServicePrices()),
            'The kept cart item is expected to watch the prices of all the merged additional services',
        );
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

        $firstProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        self::assertEquals($firstProduct->getFullName(), $cartItems[0]->getName(), 'First product name mismatch');
        self::assertEquals(2, $cartItems[0]->getQuantity(), 'First product quantity mismatch');

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);
        self::assertEquals($secondProduct->getFullName(), $cartItems[1]->getName(), 'Second product name mismatch');
        self::assertEquals(2, $cartItems[1]->getQuantity(), 'Second product quantity mismatch');

        $oldCart = $this->cartFacade->findCartByCartIdentifier($testCartUuid);
        self::assertNull($oldCart);
    }

    public function testCartIsMergedAfterRegister(): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../_graphql/mutation/RegistrationMutation.graphql',
            [
                'email' => 'test@example.com',
                'firstName' => 'Test',
                'lastName' => 'Test',
                'password' => 'testTEST123',
                'telephone' => new PhoneData('CZ', '+420', '145612314'),
                'newsletterSubscription' => false,
                'street' => '123 Fake Street',
                'city' => 'Springfield',
                'postcode' => '12345',
                'companyCustomer' => false,
                'country' => 'CZ',
                'previousCartUuid' => CartDataFixture::CART_UUID,
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'Register');

        $cart = $this->findCartOfCustomerByEmail('test@example.com');

        self::assertNotNull($cart);

        self::assertFalse($data['showCartMergeInfo']);

        $cartItems = $cart->getItems();
        self::assertCount(2, $cartItems);

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        self::assertEquals($secondProduct->getFullName(), $cartItems[0]->getName(), 'Second product name mismatch');
        self::assertEquals(2, $cartItems[0]->getQuantity(), 'Second product quantity mismatch');

        $firstProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);
        self::assertEquals($firstProduct->getFullName(), $cartItems[1]->getName(), 'Third product name mismatch');
        self::assertEquals(2, $cartItems[1]->getQuantity(), 'Third product quantity mismatch');

        $oldCart = $this->cartFacade->findCartByCartIdentifier(CartDataFixture::CART_UUID);
        self::assertNull($oldCart);
    }

    private function getCartItemByProduct(Cart $cart, Product $product): CartItem
    {
        foreach ($cart->getItems() as $cartItem) {
            if ($cartItem->getProduct()->getId() === $product->getId()) {
                return $cartItem;
            }
        }

        self::fail(sprintf('The cart is expected to contain an item with the product %d', $product->getId()));
    }

    private function findCartOfCurrentCustomer(): ?Cart
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);

        return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
    }

    private function findCartOfCustomerByEmail(string $email): ?Cart
    {
        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->frontendCustomerUserProvider->loadUserByUsername($email);

        $customerUserIdentifier = $this->customerUserIdentifierFactory->getByCustomerUser($customerUser);

        return $this->cartFacade->findCartByCustomerUserIdentifier($customerUserIdentifier);
    }

    private function addProductToCustomerCart(Product $product, int $productQuantity): void
    {
        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => $productQuantity,
        ]);
    }
}
