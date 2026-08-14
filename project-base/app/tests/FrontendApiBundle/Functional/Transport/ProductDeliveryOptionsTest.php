<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Transport;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\StoreDataFixture;
use App\DataFixtures\Demo\TransportDataFixture;
use App\DataFixtures\Demo\VatDataFixture;
use App\Model\Product\Product;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\FrameworkBundle\Model\Transport\DeliveryDate\TransportExpectedDeliveryDateCalculation;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class ProductDeliveryOptionsTest extends GraphQlTestCase
{
    /**
     * '9177759' from the demo data — in stock, 3 kg, excludes the drone delivery
     */
    private const int IN_STOCK_PRODUCT_REFERENCE_ID = 1;

    /**
     * '9176508' from the demo data — restricted to personal pickup only
     */
    private const int PERSONAL_PICKUP_ONLY_PRODUCT_REFERENCE_ID = 2;

    /**
     * 'PROMO3PLUS1' from the demo data — in stock, 12 kg, heavier than every Czech post weight tier
     */
    private const int HEAVY_PRODUCT_REFERENCE_ID = 154;

    /**
     * '5966179' from the demo data — selling denied
     */
    private const int SELLING_DENIED_PRODUCT_REFERENCE_ID = 6;

    /**
     * '718253' from the demo data — sold out with an already passed restocking date
     */
    private const int SOLD_OUT_WITHOUT_VALID_RESTOCKING_DATE_PRODUCT_REFERENCE_ID = 21;

    /**
     * '7700769XCX' from the demo data — a main variant
     */
    private const int MAIN_VARIANT_REFERENCE_ID = 83;

    private const string UNKNOWN_UUID = '00000000-0000-0000-0000-000000000000';

    /**
     * @inject
     */
    private TransportExpectedDeliveryDateCalculation $transportExpectedDeliveryDateCalculation;

    public function testTransportExcludedForTheProductIsNotReturned(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::IN_STOCK_PRODUCT_REFERENCE_ID, Product::class);
        $droneName = t('Drone delivery', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());

        $transportNames = array_keys($this->getDeliveryOptionsForProductIndexedByTransportName($product));

        $this->assertNotEmpty($transportNames);
        $this->assertNotContains($droneName, $transportNames);
    }

    public function testOnlyPersonalPickupTransportsAreReturnedForPersonalPickupOnlyProduct(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::PERSONAL_PICKUP_ONLY_PRODUCT_REFERENCE_ID, Product::class);

        $deliveryOptions = $this->getDeliveryOptionsForProduct($product);

        $this->assertNotEmpty($deliveryOptions);

        foreach ($deliveryOptions as $deliveryOption) {
            $this->assertSame(TransportTypeEnum::TYPE_PERSONAL_PICKUP, $deliveryOption['transport']['transportTypeCode']);
        }
    }

    public function testTransportOverProductWeightIsNotReturnedAtAll(): void
    {
        $lightProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::IN_STOCK_PRODUCT_REFERENCE_ID, Product::class);
        $heavyProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::HEAVY_PRODUCT_REFERENCE_ID, Product::class);
        $czechPostName = t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());

        $lightProductTransportNames = array_keys($this->getDeliveryOptionsForProductIndexedByTransportName($lightProduct));
        $heavyProductTransportNames = array_keys($this->getDeliveryOptionsForProductIndexedByTransportName($heavyProduct));

        $this->assertContains($czechPostName, $lightProductTransportNames);
        $this->assertNotContains($czechPostName, $heavyProductTransportNames);
    }

    public function testTransportPriceUsesTheLowestWeightTierSatisfyingTheProductWeight(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::IN_STOCK_PRODUCT_REFERENCE_ID, Product::class);
        $vatHigh = $this->getReferenceForDomain(VatDataFixture::VAT_HIGH, $this->domain->getId(), Vat::class);
        $czechPostName = t('Czech post', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());

        $deliveryOptionsByTransportName = $this->getDeliveryOptionsForProductIndexedByTransportName($product);

        // the 3 kg product satisfies the first Czech post weight tier, so the higher tier price must not be used
        $this->assertSame(
            $this->getSerializedPriceConvertedToDomainDefaultCurrency('100', $vatHigh),
            $deliveryOptionsByTransportName[$czechPostName]['price'],
        );
    }

    public function testTransportIsFreeForProductPriceReachingTheFreeTransportLimit(): void
    {
        $this->pricingSetting->setFreeTransportAndPaymentPriceLimit($this->domain->getId(), Money::create(1));

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::IN_STOCK_PRODUCT_REFERENCE_ID, Product::class);
        $pplName = t('PPL', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());

        $deliveryOptionsByTransportName = $this->getDeliveryOptionsForProductIndexedByTransportName($product);

        $this->assertSame('0.000000', $deliveryOptionsByTransportName[$pplName]['price']['priceWithVat']);
    }

    /**
     * @see \Tests\FrameworkBundle\Unit\Model\Transport\DeliveryDate\TransportExpectedDeliveryDateCalculationTest for the correctness of the calculation itself
     */
    public function testExpectedDeliveryDateIsNullForSoldOutProductWithoutValidRestockingDate(): void
    {
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::SOLD_OUT_WITHOUT_VALID_RESTOCKING_DATE_PRODUCT_REFERENCE_ID, Product::class);

        $deliveryOptionsByTransportName = $this->getDeliveryOptionsForProductIndexedByTransportName($product);

        $this->assertNull($deliveryOptionsByTransportName[$transport->getName($this->getFirstDomainLocale())]['expectedDeliveryDate']);
    }

    public function testStoreExpectedDeliveryDateForProductMatchesTheCalculation(): void
    {
        $personalPickupTransport = $this->getReference(TransportDataFixture::TRANSPORT_PERSONAL, Transport::class);
        $store = $this->getReference(StoreDataFixture::STORE_PREFIX . 1, Store::class);
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::IN_STOCK_PRODUCT_REFERENCE_ID, Product::class);

        $storeNodesByUuid = $this->getDeliveryStoreNodesIndexedByStoreUuid($product, $personalPickupTransport);

        $expectedDeliveryDate = $this->transportExpectedDeliveryDateCalculation
            ->calculateExpectedDeliveryDateForStoreAndProduct($personalPickupTransport, $product, $this->domain->getId(), $store)
            ?->format(DATE_ATOM);

        $this->assertNotNull($expectedDeliveryDate);
        $this->assertArrayHasKey($store->getUuid(), $storeNodesByUuid);
        $this->assertSame($expectedDeliveryDate, $storeNodesByUuid[$store->getUuid()]['expectedDeliveryDate']);
    }

    public function testStoresForTransportWithoutPersonalPickupReturnUserError(): void
    {
        $transport = $this->getReference(TransportDataFixture::TRANSPORT_PPL, Transport::class);
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::IN_STOCK_PRODUCT_REFERENCE_ID, Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductDeliveryStoresQuery.graphql', [
            'productUuid' => $product->getUuid(),
            'transportUuid' => $transport->getUuid(),
        ]);

        $this->assertUserError($response, 'invalid-argument');
    }

    public function testUnknownProductUuidReturnsUserError(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductDeliveryOptionsQuery.graphql', [
            'productUuid' => self::UNKNOWN_UUID,
        ]);

        $this->assertUserError($response, 'product-not-found');
    }

    public function testUnknownTransportUuidReturnsUserError(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::IN_STOCK_PRODUCT_REFERENCE_ID, Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductDeliveryStoresQuery.graphql', [
            'productUuid' => $product->getUuid(),
            'transportUuid' => self::UNKNOWN_UUID,
        ]);

        $this->assertUserError($response, 'transport-not-found');
    }

    public function testSellingDeniedProductReturnsUserError(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::SELLING_DENIED_PRODUCT_REFERENCE_ID, Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductDeliveryOptionsQuery.graphql', [
            'productUuid' => $product->getUuid(),
        ]);

        $this->assertUserError($response, 'product-not-found');
    }

    public function testMainVariantUuidReturnsUserError(): void
    {
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::MAIN_VARIANT_REFERENCE_ID, Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductDeliveryOptionsQuery.graphql', [
            'productUuid' => $mainVariant->getUuid(),
        ]);

        $this->assertUserError($response, 'product-not-found');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getDeliveryOptionsForProduct(Product $product): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductDeliveryOptionsQuery.graphql', [
            'productUuid' => $product->getUuid(),
        ]);

        return $this->getResponseDataForGraphQlType($response, 'productDeliveryOptions');
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getDeliveryOptionsForProductIndexedByTransportName(Product $product): array
    {
        $deliveryOptions = $this->getDeliveryOptionsForProduct($product);
        $transportNames = array_map(
            static fn (array $deliveryOption): string => $deliveryOption['transport']['name'],
            $deliveryOptions,
        );

        return array_combine($transportNames, $deliveryOptions);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getDeliveryStoreNodesIndexedByStoreUuid(Product $product, Transport $transport): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductDeliveryStoresQuery.graphql', [
            'productUuid' => $product->getUuid(),
            'transportUuid' => $transport->getUuid(),
            'first' => 100,
        ]);

        $storeNodes = array_column(
            $this->getResponseDataForGraphQlType($response, 'productDeliveryStores')['edges'],
            'node',
        );

        return array_combine(
            array_map(static fn (array $storeNode): string => $storeNode['store']['uuid'], $storeNodes),
            $storeNodes,
        );
    }
}
