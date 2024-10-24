<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
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
use Shopsys\FrameworkBundle\Model\Product\Product;
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
        $promoCodeData->discountType = PromoCodeTypeEnum::PERCENT;
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
            new Price(Money::create(10), Money::create('12.1')),
            new Price(Money::create(100), Money::create(121)),
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

    /**
     * @param string|null $promoCodeType
     */
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

    /**
     * @return iterable
     */
    public static function invalidPromoCodeTypeDataProvider(): iterable
    {
        yield [PromoCodeTypeEnum::NOMINAL];

        yield [null];
    }

    /**
     * @param array $discountPrices
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\ApplyPercentagePromoCodeMiddleware
     */
    private function createApplyPercentagePromoCodeMiddleware(
        array $discountPrices,
    ): ApplyPercentagePromoCodeMiddleware {
        $currentPromoCodeFacade = $this->createMock(CurrentPromoCodeFacade::class);
        $currentPromoCodeFacade->method('validatePromoCode')->willReturn([1, 2]);

        $promoCodeFacade = $this->createMock(PromoCodeFacade::class);
        $promoCodeFacade->method('getHighestLimitByPromoCodeAndTotalPrice')->willReturn(new PromoCodeLimit('1', '10'));

        $discountCalculation = $this->createMock(DiscountCalculation::class);
        $discountCalculation->method('calculatePercentageDiscountRoundedByCurrency')->willReturnOnConsecutiveCalls(...$discountPrices);

        $numberFormatterExtension = $this->createMock(NumberFormatterExtension::class);
        $numberFormatterExtension->method('formatPercent')->willReturn('10%');

        return new ApplyPercentagePromoCodeMiddleware(
            $currentPromoCodeFacade,
            $promoCodeFacade,
            $this->createCurrencyFacade(),
            $discountCalculation,
            $numberFormatterExtension,
            $this->createOrderItemDataFactory(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param array<int, array{unitPrice: \Shopsys\FrameworkBundle\Model\Pricing\Price, quantity: int, name: string, id: int}> $productsTestInputData
     */
    private function addProductsToOrderData(OrderData $orderData, array $productsTestInputData): void
    {
        foreach ($productsTestInputData as $productTestInputData) {
            $unitPrice = $productTestInputData['unitPrice'];
            $quantity = $productTestInputData['quantity'];
            $productName = $productTestInputData['name'];
            $productId = $productTestInputData['id'];

            $productItemData = new OrderItemData();
            $productItemData->type = OrderItemTypeEnum::TYPE_PRODUCT;
            $productItemData->name = $productName;
            $productItemData->setUnitPrice($unitPrice);
            $productItemData->setTotalPrice($unitPrice->multiply($quantity));
            $productItemData->vatPercent = '21';
            $productItemData->quantity = $quantity;
            $productItemData->unitName = 'pcs';
            $productItemData->catnum = (string)$productId;
            $productItemData->product = $this->createMock(Product::class);
            $productItemData->product->method('getId')->willReturn($productId);

            $orderData->addItem($productItemData);
        }
    }
}
