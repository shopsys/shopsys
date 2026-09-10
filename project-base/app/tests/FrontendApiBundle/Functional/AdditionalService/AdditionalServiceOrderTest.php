<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\AdditionalService;

use App\DataFixtures\Demo\AdditionalServiceDataFixture;
use App\DataFixtures\Demo\CartDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Cart\Cart;
use App\Model\Cart\CartFacade;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use DateTimeImmutable;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceDataFactory;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceFacade;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServicePriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Transport\DeliveryDate\TransportExpectedDeliveryDateCalculation;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Tests\FrameworkBundle\Test\IsMoneyEqual;
use Tests\FrontendApiBundle\Functional\Order\OrderTestTrait;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class AdditionalServiceOrderTest extends GraphQlTestCase
{
    use OrderTestTrait;

    private const string DEMO_CART_ITEM_UUID_PRODUCT_1 = '5096bd50-45e1-40a6-bbe8-6192592feb56';
    private const string DEMO_CART_ITEM_UUID_PRODUCT_72 = 'f0d0cb7c-f873-4107-8187-f733d292b02f';

    /**
     * @inject
     */
    private CartFacade $cartFacade;

    /**
     * @inject
     */
    private OrderApiFacade $orderApiFacade;

    /**
     * @inject
     */
    private AdditionalServiceFacade $additionalServiceFacade;

    /**
     * @inject
     */
    private AdditionalServicePriceCalculation $additionalServicePriceCalculation;

    /**
     * @inject
     */
    private OrderItemPriceCalculation $orderItemPriceCalculation;

    /**
     * @inject
     */
    private AdditionalServiceDataFactory $additionalServiceDataFactory;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @inject
     */
    private TransportExpectedDeliveryDateCalculation $transportExpectedDeliveryDateCalculation;

    private AdditionalService $assemblyAdditionalService;

    private AdditionalService $giftWrappingAdditionalService;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->assemblyAdditionalService = $this->getReference(
            AdditionalServiceDataFixture::ADDITIONAL_SERVICE_ASSEMBLY,
            AdditionalService::class,
        );
        $this->giftWrappingAdditionalService = $this->getReference(
            AdditionalServiceDataFixture::ADDITIONAL_SERVICE_GIFT_WRAPPING,
            AdditionalService::class,
        );
    }

    public function testOrderContainsAdditionalServiceItemsRelatedToTheirProductItems(): void
    {
        $cart = $this->cartFacade->findCartByCartIdentifier(CartDataFixture::CART_UUID);

        $this->cartFacade->setItemAdditionalServicesInExistingCartByUuid(
            self::DEMO_CART_ITEM_UUID_PRODUCT_1,
            [$this->assemblyAdditionalService, $this->giftWrappingAdditionalService],
            $cart,
        );
        $this->cartFacade->setItemAdditionalServicesInExistingCartByUuid(
            self::DEMO_CART_ITEM_UUID_PRODUCT_72,
            [$this->giftWrappingAdditionalService],
            $cart,
        );

        $this->addCzechPostTransportToCart(CartDataFixture::CART_UUID);
        $this->addCashOnDeliveryPaymentToCart(CartDataFixture::CART_UUID);

        $expectedDeliveryDate = $this->getExpectedDeliveryDate($cart);

        $response = $this->getCreateOrderMutationResponseFromCart();
        $orderUuid = $this->getResponseDataForGraphQlType($response, 'CreateOrder')['order']['uuid'];

        $order = $this->orderApiFacade->getByUuid($orderUuid);

        self::assertEquals($expectedDeliveryDate, $order->getExpectedDeliveryDate());

        $additionalServiceItems = $this->getItemsByType($order, OrderItemTypeEnum::TYPE_ADDITIONAL_SERVICE);
        self::assertCount(3, $additionalServiceItems);

        $productItems = $this->getItemsByType($order, OrderItemTypeEnum::TYPE_PRODUCT);
        self::assertCount(2, $productItems);

        $firstProductServiceItems = $this->getRelatedItemsByType($productItems[0], OrderItemTypeEnum::TYPE_ADDITIONAL_SERVICE);
        self::assertCount(2, $firstProductServiceItems);

        $secondProductServiceItems = $this->getRelatedItemsByType($productItems[1], OrderItemTypeEnum::TYPE_ADDITIONAL_SERVICE);
        self::assertCount(1, $secondProductServiceItems);

        foreach ($additionalServiceItems as $additionalServiceItem) {
            self::assertSame(2, $additionalServiceItem->getQuantity());
            self::assertThat(
                $additionalServiceItem->getTotalPriceWithVat(),
                new IsMoneyEqual($additionalServiceItem->getUnitPriceWithVat()->multiply($additionalServiceItem->getQuantity())),
            );
        }

        $assemblyOrderItem = null;

        foreach ($firstProductServiceItems as $serviceItem) {
            if ($serviceItem->getCatnum() === 'SERVICE-ASSEMBLY') {
                $assemblyOrderItem = $serviceItem;
            }
        }

        self::assertNotNull($assemblyOrderItem);

        $expectedUnitPrice = $this->additionalServicePriceCalculation->calculatePrice(
            $this->assemblyAdditionalService,
            $productItems[0]->getProduct(),
            Domain::FIRST_DOMAIN_ID,
        );
        $expectedTotalPrice = $this->additionalServicePriceCalculation->calculateTotalPrice(
            $this->assemblyAdditionalService,
            $productItems[0]->getProduct(),
            Domain::FIRST_DOMAIN_ID,
            $assemblyOrderItem->getQuantity(),
        );

        self::assertThat($assemblyOrderItem->getUnitPriceWithoutVat(), new IsMoneyEqual($expectedUnitPrice->getPriceWithoutVat()));
        self::assertThat($assemblyOrderItem->getUnitPriceWithVat(), new IsMoneyEqual($expectedUnitPrice->getPriceWithVat()));

        $assemblyOrderItemTotalPrice = $this->orderItemPriceCalculation->calculateTotalPrice($assemblyOrderItem);

        self::assertThat($assemblyOrderItemTotalPrice->getPriceWithoutVat(), new IsMoneyEqual($expectedTotalPrice->getPriceWithoutVat()));
        self::assertThat($assemblyOrderItemTotalPrice->getPriceWithVat(), new IsMoneyEqual($expectedTotalPrice->getPriceWithVat()));

        $this->assertAdditionalServiceVatRates($productItems, $firstProductServiceItems, $secondProductServiceItems);
        $this->assertOrderItemsFromApi($order);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[] $productItems
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[] $firstProductServiceItems
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[] $secondProductServiceItems
     */
    private function assertAdditionalServiceVatRates(
        array $productItems,
        array $firstProductServiceItems,
        array $secondProductServiceItems,
    ): void {
        $vatPercentsByCatnum = [];

        foreach ($secondProductServiceItems as $serviceItem) {
            $vatPercentsByCatnum[$serviceItem->getCatnum()] = $serviceItem->getVatPercent();
        }

        self::assertSame(
            $productItems[1]->getVatPercent(),
            $vatPercentsByCatnum['SERVICE-GIFT-WRAP'],
            'The additional service inherits the VAT rate of the product it accompanies',
        );

        foreach ($firstProductServiceItems as $serviceItem) {
            self::assertSame($productItems[0]->getVatPercent(), $serviceItem->getVatPercent());
            self::assertNotNull($serviceItem->getAdditionalService());
        }
    }

    private function assertOrderItemsFromApi(Order $order): void
    {
        $orderFromApi = $this->getOrderFromApiByUrlHash($order->getUrlHash());
        self::assertSame($order->getExpectedDeliveryDate()?->format(DATE_ATOM), $orderFromApi['expectedDeliveryDate']);

        $orderItemsFromApi = $orderFromApi['items'];
        $productItemsFromApi = array_values(array_filter(
            $orderItemsFromApi,
            static fn (array $orderItemFromApi) => $orderItemFromApi['type'] === OrderItemTypeEnum::TYPE_PRODUCT,
        ));

        $firstProductRelatedItemCatnums = array_column($productItemsFromApi[0]['relatedItems'], 'catnum');
        sort($firstProductRelatedItemCatnums);

        self::assertSame(['SERVICE-ASSEMBLY', 'SERVICE-GIFT-WRAP'], $firstProductRelatedItemCatnums);
        self::assertSame(['SERVICE-GIFT-WRAP'], array_column($productItemsFromApi[1]['relatedItems'], 'catnum'));

        $firstProductRelatedItemsByCatnum = array_column($productItemsFromApi[0]['relatedItems'], null, 'catnum');
        self::assertSame(1, $firstProductRelatedItemsByCatnum['SERVICE-ASSEMBLY']['deliveryDaysExtension']);
        self::assertSame(0, $firstProductRelatedItemsByCatnum['SERVICE-GIFT-WRAP']['deliveryDaysExtension']);
    }

    /**
     * @return array<string, mixed>
     */
    private function getOrderFromApiByUrlHash(string $urlHash): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/OrderItemsWithRelatedItemsQuery.graphql', [
            'urlHash' => $urlHash,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'order');
    }

    private function getExpectedDeliveryDate(Cart $cart): DateTimeImmutable
    {
        $transport = $cart->getTransport();
        self::assertNotNull($transport);
        $expectedDeliveryDate = $this->transportExpectedDeliveryDateCalculation->calculateExpectedDeliveryDate(
            $transport,
            $cart,
            Domain::FIRST_DOMAIN_ID,
        );
        self::assertNotNull($expectedDeliveryDate);

        return $expectedDeliveryDate;
    }

    public function testSeparateSupplyAdditionalServiceKeepsItsOwnVatRate(): void
    {
        $zeroVat = $this->getReferenceForDomain(VatDataFixture::VAT_ZERO, Domain::FIRST_DOMAIN_ID, Vat::class);
        $separateSupplyAdditionalService = $this->createSeparateSupplyAdditionalService($zeroVat);
        $this->assignAdditionalServiceToFirstCartProduct($separateSupplyAdditionalService);

        $separateSupplyAdditionalService = $this->additionalServiceFacade->getById($separateSupplyAdditionalService->getId());

        $cart = $this->cartFacade->findCartByCartIdentifier(CartDataFixture::CART_UUID);
        $this->cartFacade->setItemAdditionalServicesInExistingCartByUuid(
            self::DEMO_CART_ITEM_UUID_PRODUCT_1,
            [$separateSupplyAdditionalService],
            $cart,
        );

        $this->addCzechPostTransportToCart(CartDataFixture::CART_UUID);
        $this->addCashOnDeliveryPaymentToCart(CartDataFixture::CART_UUID);

        $response = $this->getCreateOrderMutationResponseFromCart();
        $orderUuid = $this->getResponseDataForGraphQlType($response, 'CreateOrder')['order']['uuid'];
        $order = $this->orderApiFacade->getByUuid($orderUuid);

        $separateSupplyServiceItem = null;

        foreach ($this->getItemsByType($order, OrderItemTypeEnum::TYPE_ADDITIONAL_SERVICE) as $additionalServiceItem) {
            if ($additionalServiceItem->getCatnum() === 'SERVICE-SEPARATE-SUPPLY') {
                $separateSupplyServiceItem = $additionalServiceItem;

                break;
            }
        }

        self::assertNotNull($separateSupplyServiceItem);
        self::assertSame(
            $zeroVat->getPercent(),
            $separateSupplyServiceItem->getVatPercent(),
            'The separate supply additional service uses its own VAT rate, not the VAT rate of the product',
        );

        $parentProductItem = $this->getItemsByType($order, OrderItemTypeEnum::TYPE_PRODUCT)[0];
        self::assertNotSame(
            $parentProductItem->getVatPercent(),
            $separateSupplyServiceItem->getVatPercent(),
            'The separate supply VAT rate differs from the VAT rate of the accompanied product',
        );
    }

    public function testAdditionalServiceNoLongerOfferedByProductIsNotAddedToOrder(): void
    {
        $cart = $this->cartFacade->findCartByCartIdentifier(CartDataFixture::CART_UUID);
        $this->cartFacade->setItemAdditionalServicesInExistingCartByUuid(
            self::DEMO_CART_ITEM_UUID_PRODUCT_1,
            [$this->assemblyAdditionalService],
            $cart,
        );

        $this->unassignAllAdditionalServicesFromFirstCartProduct();

        $this->addCzechPostTransportToCart(CartDataFixture::CART_UUID);
        $this->addCashOnDeliveryPaymentToCart(CartDataFixture::CART_UUID);

        $response = $this->getCreateOrderMutationResponseFromCart();
        $orderUuid = $this->getResponseDataForGraphQlType($response, 'CreateOrder')['order']['uuid'];
        $order = $this->orderApiFacade->getByUuid($orderUuid);

        $assemblyServiceItems = array_filter(
            $this->getItemsByType($order, OrderItemTypeEnum::TYPE_ADDITIONAL_SERVICE),
            static fn (OrderItem $orderItem) => $orderItem->getCatnum() === 'SERVICE-ASSEMBLY',
        );

        self::assertCount(
            0,
            $assemblyServiceItems,
            'An additional service no longer offered by the product is not turned into an order item',
        );
    }

    private function unassignAllAdditionalServicesFromFirstCartProduct(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->additionalServicesByDomainId[Domain::FIRST_DOMAIN_ID] = [];

        $this->productFacade->edit($product->getId(), $productData);
        $this->handleDispatchedRecalculationMessages();
    }

    private function createSeparateSupplyAdditionalService(Vat $vatOnFirstDomain): AdditionalService
    {
        $additionalServiceData = $this->additionalServiceDataFactory->create();
        $additionalServiceData->catnum = 'SERVICE-SEPARATE-SUPPLY';

        foreach (array_keys($additionalServiceData->name) as $locale) {
            $additionalServiceData->name[$locale] = 'Separate supply service';
        }

        foreach (array_keys($additionalServiceData->enabledByDomainId) as $domainId) {
            $additionalServiceData->pricesIndexedByDomainId[$domainId] = Money::create(100);
        }

        $additionalServiceData->useProductVatRateByDomainId[Domain::FIRST_DOMAIN_ID] = false;
        $additionalServiceData->vatsIndexedByDomainId[Domain::FIRST_DOMAIN_ID] = $vatOnFirstDomain;

        return $this->additionalServiceFacade->create($additionalServiceData);
    }

    private function assignAdditionalServiceToFirstCartProduct(AdditionalService $additionalService): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->additionalServicesByDomainId[Domain::FIRST_DOMAIN_ID][] = $additionalService;

        $this->productFacade->edit($product->getId(), $productData);
        $this->handleDispatchedRecalculationMessages();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    private function getItemsByType(Order $order, string $type): array
    {
        return array_values(array_filter(
            $order->getItems(),
            static fn (OrderItem $orderItem) => $orderItem->getType() === $type,
        ));
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    private function getRelatedItemsByType(OrderItem $orderItem, string $type): array
    {
        return array_values(array_filter(
            $orderItem->getRelatedItems(),
            static fn (OrderItem $relatedItem) => $relatedItem->getType() === $type,
        ));
    }
}
