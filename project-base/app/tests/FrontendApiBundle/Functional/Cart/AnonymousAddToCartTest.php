<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Cart\CartFacade;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Product;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class AnonymousAddToCartTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private CartFacade $cartFacade;

    private Product $testingProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
    }

    public function testNewCartIsCreated(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $cart = $this->cartFacade->findCartByCartIdentifier($newlyCreatedCart['uuid']);

        self::assertNotNull($cart);

        $cartItems = $cart->getItems();

        self::assertCount(1, $cartItems);
        self::assertEquals($productQuantity, $cartItems[0]->getQuantity());
    }

    public function testProductIsAddedToExistingCart(): void
    {
        $initialProductQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($initialProductQuantity);

        $addedProductQuantity = 3;

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $newlyCreatedCart['uuid'],
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => $addedProductQuantity,
        ]);

        self::assertEquals($newlyCreatedCart['uuid'], $response['data']['AddToCart']['cart']['uuid']);

        $cart = $this->cartFacade->findCartByCartIdentifier($newlyCreatedCart['uuid']);
        self::assertNotNull($cart);

        $cartItems = $cart->getItems();
        self::assertCount(1, $cartItems);
        self::assertEquals($initialProductQuantity + $addedProductQuantity, $cartItems[0]->getQuantity());
    }

    public function testAnotherProductIsAddedToCart(): void
    {
        $productQuantity = 2;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72);
        $secondProductQuantity = 5;

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $newlyCreatedCart['uuid'],
            'productUuid' => $secondProduct->getUuid(),
            'quantity' => $secondProductQuantity,
        ]);

        self::assertEquals($newlyCreatedCart['uuid'], $response['data']['AddToCart']['cart']['uuid']);

        $cart = $this->cartFacade->findCartByCartIdentifier($newlyCreatedCart['uuid']);
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
        $newlyCreatedCart = $this->addTestingProductToNewCart($initialProductQuantity);

        $desiredProductQuantity = 3;

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $newlyCreatedCart['uuid'],
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => $desiredProductQuantity,
            'isAbsoluteQuantity' => true,
        ]);

        self::assertEquals($newlyCreatedCart['uuid'], $response['data']['AddToCart']['cart']['uuid']);

        $cart = $this->cartFacade->findCartByCartIdentifier($newlyCreatedCart['uuid']);
        self::assertNotNull($cart);

        $cartItems = $cart->getItems();
        self::assertCount(1, $cartItems);
        self::assertEquals($desiredProductQuantity, $cartItems[0]->getQuantity());
    }

    /**
     * @dataProvider getInvalidQuantityProvider
     * @param mixed $invalidQuantity
     */
    public function testInvalidQuantityProvided(int $invalidQuantity): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => $invalidQuantity,
        ]);

        self::assertArrayHasKey('errors', $response);

        $violations = $this->getErrorsExtensionValidationFromResponse($response);

        self::assertArrayHasKey('input.quantity', $violations);
        self::assertEquals(GreaterThan::TOO_LOW_ERROR, $violations['input.quantity'][0]['code']);
    }

    /**
     * @return array<int, int[]>
     */
    public function getInvalidQuantityProvider(): array
    {
        return [
            [0],
            [-1],
        ];
    }

    public function testMoreQuantityThanAvailableAddedToCart(): void
    {
        $productAvailabilityFacade = self::getContainer()->get(ProductAvailabilityFacade::class);
        $maximumAvailableQuantity = $productAvailabilityFacade->getMaximumOrderQuantity($this->testingProduct, $this->domain->getId());

        $productQuantity = $maximumAvailableQuantity + 3000;
        $newlyCreatedCart = $this->addTestingProductToNewCart($productQuantity);

        $cart = $this->cartFacade->findCartByCartIdentifier($newlyCreatedCart['uuid']);

        self::assertNotNull($cart);

        $cartItems = $cart->getItems();

        self::assertCount(1, $cartItems);
        self::assertEquals($maximumAvailableQuantity, $cartItems[0]->getQuantity());
    }

    public function testInvalidProductProvided(): void
    {
        $unknownUuid = '6c42d01c-b597-4afa-b58f-f792ff00b783';
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $unknownUuid,
            'quantity' => 10,
        ]);

        self::assertArrayHasKey('errors', $response);

        self::assertEquals(
            sprintf('Product with UUID "%s" is not available', $unknownUuid),
            $response['errors'][0]['message'],
        );
    }

    public function testInvalidCartUuidCreatesNewCart(): void
    {
        $unknownUuid = '6c42d01c-b597-4afa-b58f-f792ff00b783';
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $unknownUuid,
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => 10,
        ]);

        self::assertEquals($unknownUuid, $response['data']['AddToCart']['cart']['uuid']);
    }

    /**
     * @param int $productQuantity
     * @return mixed[]
     */
    private function addTestingProductToNewCart(int $productQuantity): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => $productQuantity,
        ]);

        return $response['data']['AddToCart']['cart'];
    }
}
