<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddProductsMiddleware;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;
use Tests\FrameworkBundle\Test\IsPriceEqual;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;

class AddProductsMiddlewareTest extends MiddlewareTestCase
{
    /**
     * @param array<int, array{name: string, quantity: int, unitPrice: \Shopsys\FrameworkBundle\Model\Pricing\Price}> $productsProviderInput
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $expectedTotalPrice
     */
    #[DataProvider('productsProvider')]
    public function testProductsAreAdded(
        array $productsProviderInput,
        Price $expectedTotalPrice,
    ): void {
        $orderProcessingData = $this->createOrderProcessingData();

        $products = [];
        $quantifiedItemPrices = [];

        foreach ($productsProviderInput as $productProviderInput) {
            $productData = new ProductData();
            $productData->name = ['en' => $productProviderInput['name']];
            $productData->unit = $this->createMock(Unit::class);
            $product = Product::create($productData);

            $orderProcessingData->orderInput->addProduct($product, $productProviderInput['quantity']);
            $products[] = $product;
            $quantifiedItemPrices[] = new QuantifiedItemPrice(
                $productProviderInput['unitPrice'],
                $productProviderInput['unitPrice']->multiply($productProviderInput['quantity']),
                $this->createVat(),
            );
        }

        $addProductsMiddleware = $this->createAddProductsMiddleware($quantifiedItemPrices);

        $result = $addProductsMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_PRODUCT]),
            new IsPriceEqual($expectedTotalPrice),
        );

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual($expectedTotalPrice),
        );

        $actualProductItemsType = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT);

        $this->assertCount(count($products), $actualProductItemsType);
        $this->assertCount(count($products), $actualOrderData->items);

        foreach ($products as $index => $product) {
            $this->assertSame($product->getName('en'), $actualProductItemsType[$index]->name);
            $this->assertSame($product, $actualProductItemsType[$index]->product);
        }
    }

    /**
     * @return iterable
     */
    public static function productsProvider(): iterable
    {
        yield 'single product' => [
            'productsProviderInput' => [
                [
                    'name' => 'product1',
                    'quantity' => 1,
                    'unitPrice' => new Price(Money::create('100'), Money::create('121')),
                ],
            ],
            'expectedTotalPrice' => new Price(Money::create('100'), Money::create('121')),
        ];

        yield 'multiple products' => [
            'productsProviderInput' => [
                [
                    'name' => 'product1',
                    'quantity' => 1,
                    'unitPrice' => new Price(Money::create('100'), Money::create('121')),
                ],
                [
                    'name' => 'product2',
                    'quantity' => 1,
                    'unitPrice' => new Price(Money::create('1000'), Money::create('1210')),
                ],
            ],
            'expectedTotalPrice' => new Price(Money::create('1100'), Money::create('1331')),
        ];

        yield 'multiple products with various quantities' => [
            'productsProviderInput' => [
                [
                    'name' => 'product1',
                    'quantity' => 2,
                    'unitPrice' => new Price(Money::create('100'), Money::create('121')),
                ],
                [
                    'name' => 'product2',
                    'quantity' => 5,
                    'unitPrice' => new Price(Money::create('1000'), Money::create('1210')),
                ],
            ],
            'expectedTotalPrice' => new Price(Money::create('5200'), Money::create('6292')),
        ];
    }

    public function testNoProductIsAddedIfMissing(): void
    {
        $orderProcessingData = $this->createOrderProcessingData();

        $addProductsMiddleware = $this->createAddProductsMiddleware([]);

        $result = $addProductsMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_PRODUCT]),
            new IsPriceEqual(Price::zero()),
        );

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual(Price::zero()),
        );

        $actualProductItemsType = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT);

        $this->assertCount(0, $actualProductItemsType);
        $this->assertCount(0, $actualOrderData->items);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice[] $quantifiedItemPrices
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddProductsMiddleware
     */
    private function createAddProductsMiddleware(array $quantifiedItemPrices): AddProductsMiddleware
    {
        $quantifiedProductPriceCalculation = $this->createMock(QuantifiedProductPriceCalculation::class);
        $quantifiedProductPriceCalculation->method('calculatePrice')->willReturnOnConsecutiveCalls(...$quantifiedItemPrices);

        return new AddProductsMiddleware(
            $quantifiedProductPriceCalculation,
            $this->createOrderItemDataFactory(),
        );
    }
}
