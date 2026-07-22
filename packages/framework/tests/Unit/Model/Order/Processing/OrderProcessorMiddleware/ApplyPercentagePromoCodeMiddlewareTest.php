<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\ApplyPercentagePromoCodeMiddleware;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\DiscountCalculation;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Twig\NumberFormatterExtension;
use Tests\FrameworkBundle\Test\IsPriceEqual;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

class ApplyPercentagePromoCodeMiddlewareTest extends MiddlewareTestCase
{
    use SetTranslatorTrait;

    public function testPromoCodeIsAdded(): void
    {
        $this->setTranslator();

        $orderProcessingData = $this->createOrderProcessingData();

        $promoCodeData = new PromoCodeData();
        $promoCodeData->code = 'promoCode';
        $promoCodeData->discountType = PromoCodeTypeEnum::DISCOUNT_TYPE_PERCENT;
        $promoCode = new PromoCode($promoCodeData);

        $orderProcessingData->orderInput->addPromoCode($promoCode);

        $productTestInputData = [
            [
                'unitPrice' => new Price(Money::create(100), Money::create(121)),
                'quantity' => 1,
                'name' => 'product 1',
                'id' => 1,
            ],
            [
                'unitPrice' => new Price(Money::create(1000), Money::create(1210)),
                'quantity' => 2,
                'name' => 'product 2',
                'id' => 2,
            ],
        ];

        $this->addProductsToOrderData(
            $orderProcessingData->orderData,
            $productTestInputData,
        );
        $orderProcessingData->orderData->addTotalPrice(new Price(Money::create(2100), Money::create(2541)), OrderItemTypeEnum::TYPE_PRODUCT);

        $applyPercentagePromoCodeMiddleware = $this->createApplyPercentagePromoCodeMiddleware([
            new Price(Money::create(10), Money::create('12.1')),
            new Price(Money::create(200), Money::create(242)),
        ]);
        $result = $applyPercentagePromoCodeMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());

        $actualOrderData = $result->orderData;

        $actualDiscountItemsType = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_DISCOUNT);

        $this->assertCount(2, $actualDiscountItemsType);
        // two already added product item data (by using addProductsToOrderData())
        $this->assertCount(4, $actualOrderData->items);

        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_DISCOUNT]),
            new IsPriceEqual(new Price(Money::create(-210), Money::create('-254.1'))),
        );

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual(new Price(Money::create(1890), Money::create('2286.9'))),
        );

        foreach ($actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_DISCOUNT) as $index => $discountItem) {
            $this->assertSame($promoCode, $discountItem->promoCode);
            $this->assertSame('Promo code -10% - ' . $productTestInputData[$index]['name'], $discountItem->name);
        }
    }

    public function testAdditionalServiceItemsAreNotDiscounted(): void
    {
        $this->setTranslator();

        $orderProcessingData = $this->createOrderProcessingData();

        $promoCodeData = new PromoCodeData();
        $promoCodeData->code = 'promoCode';
        $promoCodeData->discountType = PromoCodeTypeEnum::DISCOUNT_TYPE_PERCENT;
        $promoCode = new PromoCode($promoCodeData);

        $orderProcessingData->orderInput->addPromoCode($promoCode);

        $productItemsData = $this->addProductsToOrderData(
            $orderProcessingData->orderData,
            [
                [
                    'unitPrice' => new Price(Money::create(1000), Money::create(1210)),
                    'quantity' => 1,
                    'name' => 'product 1',
                    'id' => 1,
                ],
            ],
        );
        $orderProcessingData->orderData->addTotalPrice(new Price(Money::create(1000), Money::create(1210)), OrderItemTypeEnum::TYPE_PRODUCT);

        $this->addAdditionalServiceItemToOrderData(
            $orderProcessingData->orderData,
            $productItemsData[0],
            new Price(Money::create(100), Money::create(121)),
            'additional service',
        );

        $applyPercentagePromoCodeMiddleware = $this->createApplyPercentagePromoCodeMiddleware(
            [
                new Price(Money::create(100), Money::create(121)),
            ],
            new Price(Money::create(1000), Money::create(1210)),
        );

        $result = $applyPercentagePromoCodeMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());

        $actualOrderData = $result->orderData;
        $actualDiscountItems = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_DISCOUNT);

        $this->assertCount(1, $actualDiscountItems);
        $this->assertSame('Promo code -10% - product 1', array_first($actualDiscountItems)->name);

        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_ADDITIONAL_SERVICE]),
            new IsPriceEqual(new Price(Money::create(100), Money::create(121))),
        );
    }

    public function testAdditionalServiceItemsAreDiscountedWhenTheirTypeIsDiscountable(): void
    {
        $this->setTranslator();

        $orderProcessingData = $this->createOrderProcessingData();

        $promoCodeData = new PromoCodeData();
        $promoCodeData->code = 'promoCode';
        $promoCodeData->discountType = PromoCodeTypeEnum::DISCOUNT_TYPE_PERCENT;
        $promoCode = new PromoCode($promoCodeData);

        $orderProcessingData->orderInput->addPromoCode($promoCode);

        $productItemsData = $this->addProductsToOrderData(
            $orderProcessingData->orderData,
            [
                [
                    'unitPrice' => new Price(Money::create(1000), Money::create(1210)),
                    'quantity' => 1,
                    'name' => 'product 1',
                    'id' => 1,
                ],
            ],
        );
        $orderProcessingData->orderData->addTotalPrice(new Price(Money::create(1000), Money::create(1210)), OrderItemTypeEnum::TYPE_PRODUCT);

        $this->addAdditionalServiceItemToOrderData(
            $orderProcessingData->orderData,
            $productItemsData[0],
            new Price(Money::create(100), Money::create(121)),
            'additional service',
        );

        $applyPercentagePromoCodeMiddleware = $this->createApplyPercentagePromoCodeMiddleware(
            [
                new Price(Money::create(100), Money::create(121)),
                new Price(Money::create(10), Money::create('12.1')),
            ],
            new Price(Money::create(1100), Money::create(1331)),
            [OrderItemTypeEnum::TYPE_PRODUCT, OrderItemTypeEnum::TYPE_ADDITIONAL_SERVICE],
        );

        $result = $applyPercentagePromoCodeMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());

        $actualOrderData = $result->orderData;
        $actualDiscountItems = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_DISCOUNT);

        $this->assertCount(2, $actualDiscountItems);
        $this->assertSame('Promo code -10% - product 1', $actualDiscountItems[0]->name);
        $this->assertSame('Promo code -10% - additional service', $actualDiscountItems[1]->name);

        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_DISCOUNT]),
            new IsPriceEqual(new Price(Money::create(-110), Money::create('-133.1'))),
        );

        $this->assertCount(3, $productItemsData[0]->relatedOrderItemsData);
    }

    #[DataProvider('invalidPromoCodeTypeDataProvider')]
    public function testNoPromoCodeIsAdded(?string $promoCodeType): void
    {
        $orderProcessingData = $this->createOrderProcessingData();

        if ($promoCodeType !== null) {
            $promoCodeData = new PromoCodeData();
            $promoCodeData->code = 'promoCode';
            $promoCodeData->discountType = $promoCodeType;
            $promoCode = new PromoCode($promoCodeData);

            $orderProcessingData->orderInput->addPromoCode($promoCode);
        }

        $this->addProductsToOrderData(
            $orderProcessingData->orderData,
            [
                [
                    'unitPrice' => new Price(Money::create(100), Money::create(121)),
                    'quantity' => 1,
                    'name' => 'product 1',
                    'id' => 1,
                ],
                [
                    'unitPrice' => new Price(Money::create(1000), Money::create(1210)),
                    'quantity' => 2,
                    'name' => 'product 2',
                    'id' => 2,
                ],
            ],
        );

        $applyPercentagePromoCodeMiddleware = $this->createApplyPercentagePromoCodeMiddleware([]);

        $result = $applyPercentagePromoCodeMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());

        $actualOrderData = $result->orderData;

        $actualDiscountItemsType = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_DISCOUNT);

        $this->assertCount(0, $actualDiscountItemsType);
        // two already added product item data (by using addProductsToOrderData())
        $this->assertCount(2, $actualOrderData->items);

        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_DISCOUNT]),
            new IsPriceEqual(Price::zero()),
        );

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual(Price::zero()),
        );
    }

    public static function invalidPromoCodeTypeDataProvider(): iterable
    {
        yield [PromoCodeTypeEnum::DISCOUNT_TYPE_NOMINAL];

        yield [PromoCodeTypeEnum::DISCOUNT_TYPE_FREE_TRANSPORT_PAYMENT];

        yield [null];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price[] $discountPrices
     * @param string[]|null $discountableItemTypes
     */
    private function createApplyPercentagePromoCodeMiddleware(
        array $discountPrices,
        ?Price $expectedValidatedTotalPrice = null,
        ?array $discountableItemTypes = null,
    ): ApplyPercentagePromoCodeMiddleware {
        if ($expectedValidatedTotalPrice === null) {
            $currentPromoCodeFacade = $this->createStub(CurrentPromoCodeFacade::class);
            $currentPromoCodeFacade->method('validatePromoCode')->willReturn([1, 2]);
        } else {
            $currentPromoCodeFacade = $this->createMock(CurrentPromoCodeFacade::class);
            $currentPromoCodeFacade->expects($this->once())
                ->method('validatePromoCode')
                ->with($this->anything(), new IsPriceEqual($expectedValidatedTotalPrice), $this->anything())
                ->willReturn([1, 2]);
        }

        $promoCodeFacade = $this->createStub(PromoCodeFacade::class);
        $promoCodeFacade->method('getHighestLimitByPromoCodeAndTotalPrice')->willReturn(new PromoCodeLimit('1', '10'));

        $discountCalculation = $this->createStub(DiscountCalculation::class);
        $discountCalculation->method('calculatePercentageDiscountRoundedByCurrency')->willReturnOnConsecutiveCalls(...array_values($discountPrices));

        $numberFormatterExtension = $this->createStub(NumberFormatterExtension::class);
        $numberFormatterExtension->method('formatPercent')->willReturn('10%');

        $middlewareDependencies = [
            $currentPromoCodeFacade,
            $promoCodeFacade,
            $this->createCurrencyFacade(),
            $discountCalculation,
            $numberFormatterExtension,
            $this->createOrderItemDataFactory(),
        ];

        if ($discountableItemTypes === null) {
            return new ApplyPercentagePromoCodeMiddleware(...$middlewareDependencies);
        }

        $applyPercentagePromoCodeMiddleware = new class(...$middlewareDependencies) extends ApplyPercentagePromoCodeMiddleware {
            /**
             * @var string[]
             */
            public array $discountableItemTypes = [];

            /**
             * {@inheritdoc}
             */
            #[Override]
            protected function getDiscountableItemTypes(): array
            {
                return $this->discountableItemTypes;
            }
        };
        $applyPercentagePromoCodeMiddleware->discountableItemTypes = $discountableItemTypes;

        return $applyPercentagePromoCodeMiddleware;
    }

    /**
     * @param array<int, array{unitPrice: \Shopsys\FrameworkBundle\Model\Pricing\Price, quantity: int, name: string, id: int}> $productsTestInputData
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData[]
     */
    private function addProductsToOrderData(OrderData $orderData, array $productsTestInputData): array
    {
        $productItemsData = [];

        foreach ($productsTestInputData as $productTestInputData) {
            $productItemsData[] = $this->addProductItemToOrderData(
                $orderData,
                $productTestInputData['unitPrice'],
                $productTestInputData['quantity'],
                $productTestInputData['name'],
                $productTestInputData['id'],
            );
        }

        return $productItemsData;
    }
}
