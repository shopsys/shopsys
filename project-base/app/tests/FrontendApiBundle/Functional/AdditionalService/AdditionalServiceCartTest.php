<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\AdditionalService;

use App\DataFixtures\Demo\AdditionalServiceDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class AdditionalServiceCartTest extends GraphQlTestCase
{
    private Product $testingProduct;

    private AdditionalService $assemblyAdditionalService;

    private AdditionalService $extendedWarrantyAdditionalService;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $this->assemblyAdditionalService = $this->getReference(
            AdditionalServiceDataFixture::ADDITIONAL_SERVICE_ASSEMBLY,
            AdditionalService::class,
        );
        $this->extendedWarrantyAdditionalService = $this->getReference(
            AdditionalServiceDataFixture::ADDITIONAL_SERVICE_EXTENDED_WARRANTY,
            AdditionalService::class,
        );
    }

    public function testSetCartItemAdditionalServicesIncreasesCartTotalPrice(): void
    {
        $productQuantity = 2;
        $cartResponse = $this->addTestingProductToNewCart($productQuantity);
        $totalPriceWithVatWithoutAdditionalServices = $cartResponse['totalPrice']['priceWithVat'];
        $totalItemsPriceWithVatWithoutAdditionalServices = $cartResponse['totalItemsPrice']['priceWithVat'];

        $response = $this->setAdditionalServicesToCartItem(
            $cartResponse['uuid'],
            $cartResponse['items'][0]['uuid'],
            [$this->assemblyAdditionalService->getUuid(), $this->extendedWarrantyAdditionalService->getUuid()],
        );

        $cart = $this->getResponseDataForGraphQlType($response, 'SetCartItemAdditionalServices');

        self::assertSame(
            ['SERVICE-ASSEMBLY', 'SERVICE-WARRANTY'],
            array_column($cart['items'][0]['additionalServices'], 'catnum'),
        );

        $expectedAdditionalServicesPriceWithVat = $this->getMoneyFromCartResponse($cart['items'][0]['additionalServices'][0]['price']['priceWithVat'])
            ->add($this->getMoneyFromCartResponse($cart['items'][0]['additionalServices'][1]['price']['priceWithVat']))
            ->multiply($productQuantity);

        $expectedTotalPriceWithVat = $this->getMoneyFromCartResponse($totalPriceWithVatWithoutAdditionalServices)
            ->add($expectedAdditionalServicesPriceWithVat);

        self::assertSame(
            $expectedTotalPriceWithVat->getAmount(),
            $this->getMoneyFromCartResponse($cart['totalPrice']['priceWithVat'])->getAmount(),
        );

        $expectedTotalItemsPriceWithVat = $this->getMoneyFromCartResponse($totalItemsPriceWithVatWithoutAdditionalServices)
            ->add($expectedAdditionalServicesPriceWithVat);

        self::assertSame(
            $expectedTotalItemsPriceWithVat->getAmount(),
            $this->getMoneyFromCartResponse($cart['totalItemsPrice']['priceWithVat'])->getAmount(),
            'The first-step cart total (totalItemsPrice) includes the chosen additional services',
        );
    }

    public function testAdditionalServicesAreKeptWhenProductIsAddedAgain(): void
    {
        $cartResponse = $this->addTestingProductToNewCart(1);
        $cartUuid = $cartResponse['uuid'];
        $cartItemUuid = $cartResponse['items'][0]['uuid'];

        $this->setAdditionalServicesToCartItem(
            $cartUuid,
            $cartItemUuid,
            [$this->assemblyAdditionalService->getUuid()],
        );

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $cartUuid,
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => 1,
        ]);

        $cartItems = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart']['items'];

        self::assertCount(1, $cartItems);
        self::assertSame(2, $cartItems[0]['quantity']);
        self::assertSame(['SERVICE-ASSEMBLY'], array_column($cartItems[0]['additionalServices'], 'catnum'));
    }

    public function testAdditionalServiceNotAssignedToProductCannotBeSet(): void
    {
        $productWithoutAssemblyService = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $productWithoutAssemblyService->getUuid(),
            'quantity' => 1,
        ]);

        $cart = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart'];

        $response = $this->setAdditionalServicesToCartItem(
            $cart['uuid'],
            $cart['items'][0]['uuid'],
            [$this->assemblyAdditionalService->getUuid()],
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        self::assertSame('additional-service-invalid', $errors[0]['extensions']['userCode']);
    }

    public function testAdditionalServicesCannotBeSetOnProductGiftCartItem(): void
    {
        $productWithGift = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '14', Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $productWithGift->getUuid(),
            'quantity' => 1,
        ]);

        $cart = $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart'];
        $giftCartItems = array_values(array_filter(
            $cart['items'],
            static fn (array $cartItem) => $cartItem['type'] === 'productGift',
        ));

        self::assertNotEmpty(
            $giftCartItems,
            'The demo gift plan has to add a gift cart item, otherwise the test proves nothing',
        );

        $response = $this->setAdditionalServicesToCartItem(
            $cart['uuid'],
            array_first($giftCartItems)['uuid'],
            [$this->assemblyAdditionalService->getUuid()],
        );

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);

        self::assertSame('cart-item-invalid', $errors[0]['extensions']['userCode']);
    }

    public function testAdditionalServicesAreRemovedByProvidingEmptySet(): void
    {
        $cartResponse = $this->addTestingProductToNewCart(1);
        $cartUuid = $cartResponse['uuid'];
        $cartItemUuid = $cartResponse['items'][0]['uuid'];

        $this->setAdditionalServicesToCartItem($cartUuid, $cartItemUuid, [$this->assemblyAdditionalService->getUuid()]);
        $response = $this->setAdditionalServicesToCartItem($cartUuid, $cartItemUuid, []);

        $cart = $this->getResponseDataForGraphQlType($response, 'SetCartItemAdditionalServices');

        self::assertSame([], $cart['items'][0]['additionalServices']);
    }

    /**
     * @return array<string, mixed>
     */
    private function addTestingProductToNewCart(int $productQuantity): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => $productQuantity,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'AddToCart')['cart'];
    }

    /**
     * @param string[] $additionalServiceUuids
     * @return array<string, mixed>
     */
    private function setAdditionalServicesToCartItem(
        string $cartUuid,
        string $cartItemUuid,
        array $additionalServiceUuids,
    ): array {
        return $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/SetCartItemAdditionalServicesMutation.graphql', [
            'cartUuid' => $cartUuid,
            'cartItemUuid' => $cartItemUuid,
            'additionalServiceUuids' => $additionalServiceUuids,
        ]);
    }

    private function getMoneyFromCartResponse(string $amount): Money
    {
        return Money::create($amount);
    }
}
