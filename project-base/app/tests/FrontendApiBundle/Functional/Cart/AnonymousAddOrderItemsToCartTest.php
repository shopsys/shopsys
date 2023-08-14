<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\OrderDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\FrontendApi\Model\Cart\CartFacade;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Functional\Order\OrderTestTrait;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class AnonymousAddOrderItemsToCartTest extends GraphQlTestCase
{
    use OrderTestTrait;

    /**
     * @inject
     */
    private CartFacade $cartFacade;

    /**
     * @dataProvider notExistingCartDataProvider
     * @param bool $shouldMerge
     * @param string|null $cartUuid
     */
    public function testOrderItemsAreAddedToNotExistingCart(bool $shouldMerge, ?string $cartUuid): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '9');

        if ($cartUuid !== null) {
            $this->cartFacade->getCartCreateIfNotExists(null, $cartUuid);
        }

        $locale = $this->getLocaleForFirstDomain();
        $expectedItems = [
            [
                'quantity' => 3,
                'product' => [
                    'name' => t('Canon MG3550', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ],
            ],
            [
                'quantity' => 2,
                'product' => [
                    'name' => t('Defender 2.0 SPK-480', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ],
            ],
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/AddOrderItemsToCart.graphql',
            [
                'cartUuid' => $cartUuid,
                'orderUuid' => $order->getUuid(),
                'shouldMerge' => $shouldMerge,
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'AddOrderItemsToCart');
        $this->assertArrayHasKey('items', $data);
        $this->assertCartItems($expectedItems, $data['items']);

        $this->assertActualCartContent($data['uuid'], $expectedItems);
    }

    /**
     * @return iterable
     */
    public function notExistingCartDataProvider(): iterable
    {
        // cart is created with repeat order

        yield [true, null];

        yield [false, null];

        // cart already exists, but it is empty

        yield [true, Uuid::uuid4()->toString()];

        yield [false, Uuid::uuid4()->toString()];
    }

    public function testOrderItemsAreAddedToExistingCart(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '9');
        $cartUuid = Uuid::uuid4()->toString();
        $cart = $this->cartFacade->getCartCreateIfNotExists(null, $cartUuid);

        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        /** @var \App\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72');

        $this->cartFacade->addProductByUuidToCart($product1->getUuid(), 4, true, $cart);
        $this->cartFacade->addProductByUuidToCart($product2->getUuid(), 5, true, $cart);

        $locale = $this->getLocaleForFirstDomain();
        $expectedItems = [
            [
                'quantity' => 4,
                'product' => [
                    'name' => $product1->getName($locale),
                ],
            ],
            [
                'quantity' => 5,
                'product' => [
                    'name' => $product2->getName($locale),
                ],
            ],
            [
                'quantity' => 3,
                'product' => [
                    'name' => t('Canon MG3550', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ],
            ],
            [
                'quantity' => 2,
                'product' => [
                    'name' => t('Defender 2.0 SPK-480', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ],
            ],
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/AddOrderItemsToCart.graphql',
            [
                'cartUuid' => $cartUuid,
                'orderUuid' => $order->getUuid(),
                'shouldMerge' => true,
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'AddOrderItemsToCart');
        $this->assertArrayHasKey('items', $data);
        $this->assertCartItems($expectedItems, $data['items']);

        $this->assertActualCartContent($cartUuid, $expectedItems);
    }

    public function testOrderItemsAreReplacedInExistingCart(): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '9');
        $cartUuid = Uuid::uuid4()->toString();
        $cart = $this->cartFacade->getCartCreateIfNotExists(null, $cartUuid);

        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        /** @var \App\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '72');

        $this->cartFacade->addProductByUuidToCart($product1->getUuid(), 4, true, $cart);
        $this->cartFacade->addProductByUuidToCart($product2->getUuid(), 5, true, $cart);

        $locale = $this->getLocaleForFirstDomain();
        $expectedItems = [
            [
                'quantity' => 3,
                'product' => [
                    'name' => t('Canon MG3550', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ],
            ],
            [
                'quantity' => 2,
                'product' => [
                    'name' => t('Defender 2.0 SPK-480', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ],
            ],
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/AddOrderItemsToCart.graphql',
            [
                'cartUuid' => $cartUuid,
                'orderUuid' => $order->getUuid(),
                'shouldMerge' => false,
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'AddOrderItemsToCart');
        $this->assertArrayHasKey('items', $data);
        $this->assertCartItems($expectedItems, $data['items']);

        $this->assertActualCartContent($cartUuid, $expectedItems);
    }

    public function testNotAvailableOrderItemIsSkippedWhileAdding(): void
    {
        // order with one item that is not available
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '7');

        $locale = $this->getLocaleForFirstDomain();
        $expectedItems = [
            [
                'quantity' => 1,
                'product' => [
                    'name' => t('Canon EH-22L', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ],
            ],
            [
                'quantity' => 1,
                'product' => [
                    'name' => t('Canon EOS 700D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ],
            ],
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/AddOrderItemsToCart.graphql',
            [
                'orderUuid' => $order->getUuid(),
                'shouldMerge' => false,
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'AddOrderItemsToCart');
        $this->assertArrayHasKey('items', $data);
        $this->assertCartItems($expectedItems, $data['items']);

        $this->assertActualCartContent($data['uuid'], $expectedItems);

        $expectedNotAddedProducts = [
            [
                'name' => t('D-Link', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ],
        ];

        $this->assertSame($expectedNotAddedProducts, $data['modifications']['multipleAddedProductModifications']['notAddedProducts']);
    }

    public function testOrderItemsAreAddedFromCustomersOrder(): void
    {
        // order of registered customer
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '3');

        $locale = $this->getLocaleForFirstDomain();
        $expectedItems = [
            [
                'quantity' => 6,
                'product' => [
                    'name' => t('A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ],
            ],
            [
                'quantity' => 1,
                'product' => [
                    'name' => t('CD-R VERBATIM 210MB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ],
            ],
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/AddOrderItemsToCart.graphql',
            [
                'orderUuid' => $order->getUuid(),
                'shouldMerge' => false,
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'AddOrderItemsToCart');
        $this->assertArrayHasKey('items', $data);
        $this->assertCartItems($expectedItems, $data['items']);

        $this->assertActualCartContent($data['uuid'], $expectedItems);
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param bool $shouldMerge
     */
    public function testTheSameProductIsNotAddedAsSeparateOrderItem(bool $shouldMerge): void
    {
        $order = $this->getReference(OrderDataFixture::ORDER_PREFIX . '9');
        $cartUuid = Uuid::uuid4()->toString();
        $cart = $this->cartFacade->getCartCreateIfNotExists(null, $cartUuid);

        /** @var \App\Model\Product\Product $productDefenderSpk480 */
        $productDefenderSpk480 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '13');


        $this->cartFacade->addProductByUuidToCart($productDefenderSpk480->getUuid(), 4, true, $cart);

        $locale = $this->getLocaleForFirstDomain();
        $expectedItems = [
            [
                'quantity' => 3,
                'product' => [
                    'name' => t('Canon MG3550', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ],
            ],
            [
                'quantity' => $shouldMerge ? 6 : 2,
                'product' => [
                    'name' => t('Defender 2.0 SPK-480', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
                ],
            ],
        ];

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/AddOrderItemsToCart.graphql',
            [
                'cartUuid' => $cartUuid,
                'orderUuid' => $order->getUuid(),
                'shouldMerge' => $shouldMerge,
            ],
        );
        $data = $this->getResponseDataForGraphQlType($response, 'AddOrderItemsToCart');
        $this->assertArrayHasKey('items', $data);
        $this->assertCartItems($expectedItems, $data['items']);

        $this->assertActualCartContent($cartUuid, $expectedItems);
    }

    /**
     * @return iterable
     */
    public function trueFalseDataProvider(): iterable
    {
        yield [true];

        yield [false];
    }

    /**
     * @param string $cartUuid
     * @param array $expectedItems
     */
    private function assertActualCartContent(string $cartUuid, array $expectedItems): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/GetCart.graphql',
            ['cartUuid' => $cartUuid],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'cart');
        $this->assertArrayHasKey('items', $data);
        $this->assertCartItems($expectedItems, $data['items']);
    }

    /**
     * Compare expected items while ignoring the order of the items
     *
     * @param array $expectedItems
     * @param array $items
     */
    private function assertCartItems(array $expectedItems, array $items): void
    {
        $this->assertSameSize($expectedItems, $items);

        foreach ($expectedItems as $expectedItem) {
            $this->assertContains($expectedItem, $items);
        }
    }
}
