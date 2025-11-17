<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\ApplyPriceListDiscountMiddleware;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Tests\FrameworkBundle\Test\IsPriceEqual;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

class ApplyPriceListDiscountMiddlewareTest extends MiddlewareTestCase
{
    use SetTranslatorTrait;

    public function testNoDiscountWhenNoSpecialPrice(): void
    {
        $this->setTranslator();

        $orderProcessingData = $this->createOrderProcessingData();

        $productPrice = new Price(Money::create('100'), Money::create('121'));

        $this->addProductToOrderData(
            $orderProcessingData->orderData,
            $productPrice,
        );

        $specialPrice = null;
        $middleware = $this->createApplyPriceListDiscountMiddleware($specialPrice);

        $result = $middleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $discountItems = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_PRICE_LIST_DISCOUNT);
        $this->assertCount(0, $discountItems);

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual($productPrice),
        );
    }

    public function testDiscountAppliedForActiveSpecialPrice(): void
    {
        $this->setTranslator();

        $orderProcessingData = $this->createOrderProcessingData();

        $originalPrice = new Price(Money::create('100'), Money::create('121'));

        $productItem = $this->addProductToOrderData(
            $orderProcessingData->orderData,
            $originalPrice,
        );

        $specialPriceAmount = new Price(Money::create('80'), Money::create('96.8'));
        $specialPrice = $this->createSpecialPrice($specialPriceAmount);

        $middleware = $this->createApplyPriceListDiscountMiddleware($specialPrice);

        $result = $middleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $expectedDiscount = new Price(Money::create('-20'), Money::create('-24.2'));

        $discountItems = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_PRICE_LIST_DISCOUNT);
        $this->assertCount(1, $discountItems);

        $discountItem = $discountItems[0];
        $this->assertThat(
            $discountItem->getUnitPrice(),
            new IsPriceEqual($expectedDiscount),
        );

        $this->assertContains($discountItem, $productItem->relatedOrderItemsData);

        $expectedTotal = new Price(Money::create('80'), Money::create('96.8'));
        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual($expectedTotal),
        );
    }

    public function testNoDiscountForFutureSpecialPrice(): void
    {
        $this->setTranslator();

        $orderProcessingData = $this->createOrderProcessingData();

        $originalPrice = new Price(Money::create('100'), Money::create('121'));

        $this->addProductToOrderData(
            $orderProcessingData->orderData,
            $originalPrice,
        );

        $specialPriceAmount = new Price(Money::create('80'), Money::create('96.8'));
        $specialPrice = $this->createSpecialPrice($specialPriceAmount, true);

        $middleware = $this->createApplyPriceListDiscountMiddleware($specialPrice);

        $result = $middleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $discountItems = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_PRICE_LIST_DISCOUNT);
        $this->assertCount(0, $discountItems);

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual($originalPrice),
        );
    }

    /**
     * @param array<int, array<string, mixed>> $productsData
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $expectedTotalPrice
     * @param int $expectedDiscountItemsCount
     */
    #[DataProvider('multipleProductsProvider')]
    public function testMultipleProductsWithDifferentDiscounts(
        array $productsData,
        Price $expectedTotalPrice,
        int $expectedDiscountItemsCount,
    ): void {
        $this->setTranslator();

        $orderProcessingData = $this->createOrderProcessingData();

        $specialPrices = [];

        foreach ($productsData as $productData) {
            $productId = $productData['id'];
            $this->addProductToOrderData(
                $orderProcessingData->orderData,
                $productData['originalPrice'],
                productId: $productId,
            );

            if ($productData['specialPrice'] === null) {
                continue;
            }

            $specialPrice = $this->createSpecialPrice($productData['specialPrice']);
            $specialPrices[$productId] = $specialPrice;
        }

        $middleware = $this->createApplyPriceListDiscountMiddleware($specialPrices);

        $result = $middleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $discountItems = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_PRICE_LIST_DISCOUNT);
        $this->assertCount($expectedDiscountItemsCount, $discountItems);

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual($expectedTotalPrice),
        );
    }

    /**
     * @return iterable<string, array<string, mixed>>
     */
    public static function multipleProductsProvider(): iterable
    {
        yield 'two products with discounts' => [
            'productsData' => [
                [
                    'id' => 1,
                    'originalPrice' => new Price(Money::create('100'), Money::create('121')),
                    'specialPrice' => new Price(Money::create('80'), Money::create('96.8')),
                ],
                [
                    'id' => 2,
                    'originalPrice' => new Price(Money::create('50'), Money::create('60.5')),
                    'specialPrice' => new Price(Money::create('40'), Money::create('48.4')),
                ],
            ],
            'expectedTotalPrice' => new Price(Money::create('120'), Money::create('145.2')),
            'expectedDiscountItemsCount' => 2,
        ];

        yield 'one product with discount, one without' => [
            'productsData' => [
                [
                    'id' => 1,
                    'originalPrice' => new Price(Money::create('100'), Money::create('121')),
                    'specialPrice' => new Price(Money::create('80'), Money::create('96.8')),
                ],
                [
                    'id' => 2,
                    'originalPrice' => new Price(Money::create('50'), Money::create('60.5')),
                    'specialPrice' => null,
                ],
            ],
            'expectedTotalPrice' => new Price(Money::create('130'), Money::create('157.3')),
            'expectedDiscountItemsCount' => 1,
        ];
    }

    public function testDiscountWithQuantityMultiplier(): void
    {
        $this->setTranslator();

        $orderProcessingData = $this->createOrderProcessingData();

        $originalPrice = new Price(Money::create('100'), Money::create('121'));
        $quantity = 3;

        $this->addProductToOrderData(
            $orderProcessingData->orderData,
            $originalPrice,
            $quantity,
        );

        $specialPriceAmount = new Price(Money::create('80'), Money::create('96.8'));
        $specialPrice = $this->createSpecialPrice($specialPriceAmount);

        $middleware = $this->createApplyPriceListDiscountMiddleware($specialPrice);

        $result = $middleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $expectedDiscountPerUnit = new Price(Money::create('-20'), Money::create('-24.2'));
        $expectedTotalDiscount = $expectedDiscountPerUnit->multiply($quantity);

        $discountItems = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_PRICE_LIST_DISCOUNT);
        $this->assertCount(1, $discountItems);

        $discountItem = $discountItems[0];

        $this->assertThat(
            $discountItem->getUnitPrice(),
            new IsPriceEqual($expectedDiscountPerUnit),
        );

        $this->assertThat(
            $discountItem->getTotalPrice(),
            new IsPriceEqual($expectedTotalDiscount),
        );

        $this->assertSame($quantity, $discountItem->quantity);

        $expectedTotal = new Price(Money::create('240'), Money::create('290.4'));
        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual($expectedTotal),
        );
    }

    public function testNoProductsInOrder(): void
    {
        $this->setTranslator();

        $orderProcessingData = $this->createOrderProcessingData();

        $middleware = $this->createApplyPriceListDiscountMiddleware(null);

        $result = $middleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertCount(0, $actualOrderData->items);
        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual(Price::zero()),
        );
    }

    /**
     * @param int $productId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    private function createProduct(int $productId): Product
    {
        $product = $this->createMock(Product::class);
        $product->method('getId')->willReturn($productId);

        return $product;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param bool $isFuture
     * @return \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice
     */
    private function createSpecialPrice(Price $price, bool $isFuture = false): SpecialPrice
    {
        $specialPrice = $this->createMock(SpecialPrice::class);
        $specialPrice->method('isFuturePrice')->willReturn($isFuture);
        $specialPrice->price = $price;

        return $specialPrice;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $unitPrice
     * @param int $quantity
     * @param int $productId
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData
     */
    private function addProductToOrderData(
        OrderData $orderData,
        Price $unitPrice,
        int $quantity = 1,
        int $productId = 1,
    ): OrderItemData {
        $product = $this->createProduct($productId);

        $productItem = $this->createOrderItemDataFactory()->create(OrderItemTypeEnum::TYPE_PRODUCT);
        $productItem->product = $product;
        $productItem->name = 'Product ' . $productId;
        $productItem->quantity = $quantity;
        $productItem->setUnitPrice($unitPrice);
        $productItem->setTotalPrice($unitPrice->multiply($quantity));

        $orderData->addItem($productItem);
        $orderData->addTotalPrice($productItem->getTotalPrice(), OrderItemTypeEnum::TYPE_PRODUCT);

        return $productItem;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice|array<int, \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice>|null $specialPrice
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\ApplyPriceListDiscountMiddleware
     */
    private function createApplyPriceListDiscountMiddleware(
        SpecialPrice|array|null $specialPrice,
    ): ApplyPriceListDiscountMiddleware {
        $specialPriceFacade = $this->createMock(SpecialPriceFacade::class);

        if (is_array($specialPrice)) {
            $specialPriceFacade->method('findRelevantSpecialPrice')
                ->willReturnCallback(function ($product) use ($specialPrice) {
                    return $specialPrice[$product->getId()] ?? null;
                });
        } else {
            $specialPriceFacade->method('findRelevantSpecialPrice')->willReturn($specialPrice);
        }

        $domain = $this->createMock(Domain::class);
        $domain->method('getId')->willReturn(Domain::FIRST_DOMAIN_ID);

        return new ApplyPriceListDiscountMiddleware(
            $specialPriceFacade,
            $domain,
            $this->createOrderItemDataFactory(),
        );
    }
}
