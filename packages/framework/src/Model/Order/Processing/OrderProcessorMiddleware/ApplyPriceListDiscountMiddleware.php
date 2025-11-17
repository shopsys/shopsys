<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice;
use Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade;

class ApplyPriceListDiscountMiddleware implements OrderProcessorMiddlewareInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPriceFacade $specialPriceFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     */
    public function __construct(
        protected readonly SpecialPriceFacade $specialPriceFacade,
        protected readonly Domain $domain,
        protected readonly OrderItemDataFactory $orderItemDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack $orderProcessingStack
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData
     */
    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $orderData = $orderProcessingData->orderData;

        foreach ($orderData->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT) as $productItem) {
            $specialPrice = $this->specialPriceFacade->findRelevantSpecialPrice($productItem->product, $this->domain->getId(), $productItem->getUnitPrice());

            if ($specialPrice === null || $specialPrice->isFuturePrice()) {
                continue;
            }

            $this->addPriceListDiscountOrderItemData($orderData, $productItem, $specialPrice);
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $parentProductItem
     * @param \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice $specialPrice
     */
    protected function addPriceListDiscountOrderItemData(
        OrderData $orderData,
        OrderItemData $parentProductItem,
        SpecialPrice $specialPrice,
    ): void {
        $priceListDiscountOrderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_PRICE_LIST_DISCOUNT);

        $discountPrice = $this->calculateDiscount($parentProductItem, $specialPrice);

        $priceListDiscountOrderItemData->setUnitPrice($discountPrice);
        $priceListDiscountOrderItemData->setTotalPrice($discountPrice->multiply($parentProductItem->quantity));

        $priceListDiscountOrderItemData->quantity = $parentProductItem->quantity;
        $priceListDiscountOrderItemData->name = t('Price list discount for product catnum %productCatnum%', [
            '%productCatnum%' => $parentProductItem->product->getCatnum(),
        ]);

        $parentProductItem->relatedOrderItemsData[] = $priceListDiscountOrderItemData;

        $orderData->addItem($priceListDiscountOrderItemData);
        $orderData->addTotalPrice($priceListDiscountOrderItemData->getTotalPrice(), OrderItemTypeEnum::TYPE_PRICE_LIST_DISCOUNT);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $relatedProductItem
     * @param \Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice\SpecialPrice $specialPrice
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    protected function calculateDiscount(
        OrderItemData $relatedProductItem,
        SpecialPrice $specialPrice,
    ): PriceInterface {
        return $relatedProductItem->getUnitPrice()->subtract($specialPrice->price)->inverse();
    }
}
