<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddProductGiftsMiddleware;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddProductsMiddleware;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Pricing\QuantifiedProductPricesResult;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\Unit\Unit;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;

class AddProductGiftsMiddlewareTest extends MiddlewareTestCase
{
    public function testAddProductWithGifts(): void
    {
        $orderProcessingData = $this->createOrderProcessingData();
        $productData = new ProductData();
        $productData->name = ['en' => 'Some product'];
        $productData->unit = $this->createStub(Unit::class);
        $product = Product::create($productData);
        $quantifiedProduct = new QuantifiedProduct($product, 2);
        $quantifiedProduct->setAdditionalData(QuantifiedProduct::CART_ITEM_TYPE_KEY, CartItemTypeEnum::TYPE_PRODUCT);
        $orderProcessingData->orderInput->addQuantifiedProduct($quantifiedProduct);
        $unitPrice = new Price(Money::create(100), Money::create(121));
        $quantifiedItemPrice = new QuantifiedItemPrice(
            $unitPrice,
            $unitPrice->multiply(2),
            $this->createVat(),
        );


        $productData = new ProductData();
        $productData->name = ['en' => 'Some gift'];
        $productData->unit = $this->createStub(Unit::class);
        $product = Product::create($productData);
        $quantifiedProduct = new QuantifiedProduct($product, 2);
        $quantifiedProduct->setAdditionalData(QuantifiedProduct::CART_ITEM_TYPE_KEY, CartItemTypeEnum::TYPE_PRODUCT_GIFT);
        $orderProcessingData->orderInput->addQuantifiedProduct($quantifiedProduct);

        $giftPrice = new Price(Money::create(1), Money::create(1));
        $quantifiedGiftItemPrice = new QuantifiedItemPrice(
            $giftPrice,
            $giftPrice->multiply(2),
            $this->createVat(),
        );

        $addProductsMiddleware = $this->createAddProductsMiddleware($quantifiedItemPrice);
        $addProductGiftsMiddleware = $this->createAddProductGiftsMiddleware($quantifiedGiftItemPrice);

        $result = $addProductsMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $result = $addProductGiftsMiddleware->handle($result, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertSame(
            Money::create(2)->getAmount(),
            $actualOrderData->getItemsByType(CartItemTypeEnum::TYPE_PRODUCT_GIFT)[0]->totalPriceWithVat->getAmount(),
        );

        $this->assertSame(Money::create(244)->getAmount(), $actualOrderData->totalPrice->getPriceWithVat()->getAmount());
    }

    private function createAddProductGiftsMiddleware(
        QuantifiedItemPrice $quantifiedItemPrice,
    ): AddProductGiftsMiddleware {
        $quantifiedProductPriceCalculation = $this->createStub(QuantifiedProductPriceCalculation::class);
        $quantifiedProductPriceCalculation->method('calculateGiftPrice')->willReturn($quantifiedItemPrice);

        return new AddProductGiftsMiddleware(
            $this->createOrderItemDataFactory(),
            $quantifiedProductPriceCalculation,
        );
    }

    private function createAddProductsMiddleware(QuantifiedItemPrice $quantifiedItemPrice): AddProductsMiddleware
    {
        $quantifiedProductPriceCalculation = $this->createStub(QuantifiedProductPriceCalculation::class);
        $quantifiedProductPriceResult = new QuantifiedProductPricesResult($quantifiedItemPrice, $quantifiedItemPrice);
        $quantifiedProductPriceCalculation->method('calculateQuantifiedBasicAndSellingPrice')->willReturn($quantifiedProductPriceResult);

        return new AddProductsMiddleware(
            $quantifiedProductPriceCalculation,
            $this->createOrderItemDataFactory(),
        );
    }
}
