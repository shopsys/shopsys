<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ApplyPromotionXyMiddleware implements OrderProcessorMiddlewareInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     */
    public function __construct(
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
            $freeQuantity = $productItem->product->calculateFreeQuantity($productItem->quantity, $orderProcessingData->getDomainId());

            if ($freeQuantity === 0) {
                continue;
            }

            $this->createAndAddOrderItemData($orderData, $productItem, $freeQuantity, $orderProcessingData);
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $productItem
     * @param int $freeQuantity
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData $orderProcessingData
     */
    public function createAndAddOrderItemData(
        OrderData $orderData,
        OrderItemData $productItem,
        int $freeQuantity,
        OrderProcessingData $orderProcessingData,
    ): void {
        $discountOrderItemData = $this->createDiscountOrderItemData(
            $freeQuantity,
            $productItem,
            $orderProcessingData->getDomainConfig(),
        );

        $productItem->relatedOrderItemsData[] = $discountOrderItemData;

        $orderData->addItem($discountOrderItemData);
        $orderData->addTotalPrice($discountOrderItemData->getTotalPrice(), OrderItemTypeEnum::TYPE_PROMOTION);
        $orderData->addTotalProductPriceAdjustmentsDiscount($discountOrderItemData->getTotalPrice()->inverse());
    }

    /**
     * @param int $freeQuantity
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $productItem
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData
     */
    protected function createDiscountOrderItemData(
        int $freeQuantity,
        OrderItemData $productItem,
        DomainConfig $domainConfig,
    ): OrderItemData {
        $discountPrice = $productItem->getUnitPrice()->inverse();

        $discountOrderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_PROMOTION);

        $discountOrderItemData->name = $this->getOrderItemName($domainConfig, $productItem->product);
        $discountOrderItemData->quantity = $freeQuantity;
        $discountOrderItemData->setUnitPrice($discountPrice);
        $discountOrderItemData->setTotalPrice($discountPrice->multiply($freeQuantity));
        $discountOrderItemData->vatPercent = $productItem->vatPercent;

        return $discountOrderItemData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return string
     */
    protected function getOrderItemName(
        DomainConfig $domainConfig,
        Product $product,
    ): string {
        return t(
            'Promotion {{ x }} + {{ y }} free',
            [
                '{{ x }}' => $product->getPromotionXy($domainConfig->getId())?->getBuyQuantity(),
                '{{ y }}' => $product->getPromotionXy($domainConfig->getId())?->getFreeQuantity(),
            ],
            Translator::DEFAULT_TRANSLATION_DOMAIN,
            $domainConfig->getLocale(),
        );
    }
}
