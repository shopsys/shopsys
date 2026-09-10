<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceFacade;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServicePriceCalculation;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingStack;

class AddAdditionalServicesMiddleware implements OrderProcessorMiddlewareInterface
{
    public function __construct(
        protected readonly OrderItemDataFactory $orderItemDataFactory,
        protected readonly AdditionalServicePriceCalculation $additionalServicePriceCalculation,
        protected readonly AdditionalServiceFacade $additionalServiceFacade,
    ) {
    }

    #[Override]
    public function handle(
        OrderProcessingData $orderProcessingData,
        OrderProcessingStack $orderProcessingStack,
    ): OrderProcessingData {
        $orderData = $orderProcessingData->orderData;
        $quantifiedProducts = $orderProcessingData->orderInput->getQuantifiedProducts();
        $offeredAdditionalServiceIdsByProductId = $this->getOfferedAdditionalServiceIdsByProductId(
            $quantifiedProducts,
            $orderProcessingData->getDomainId(),
        );

        $productOrderItemsData = $orderData->getItemsByType(OrderItemTypeEnum::TYPE_PRODUCT);
        $productOrderItemIndex = 0;

        foreach ($quantifiedProducts as $quantifiedProduct) {
            if ($quantifiedProduct->getAdditionalData(QuantifiedProduct::CART_ITEM_TYPE_KEY) !== CartItemTypeEnum::TYPE_PRODUCT) {
                continue;
            }

            $productOrderItemData = $productOrderItemsData[$productOrderItemIndex] ?? null;
            $productOrderItemIndex++;

            $additionalServices = $quantifiedProduct->getAdditionalData(QuantifiedProduct::ADDITIONAL_SERVICES_KEY) ?? [];

            if ($additionalServices === []) {
                continue;
            }

            if (!$this->belongsOrderItemDataToQuantifiedProduct($productOrderItemData, $quantifiedProduct)) {
                continue;
            }

            $offeredAdditionalServiceIds = $offeredAdditionalServiceIdsByProductId[$quantifiedProduct->getProduct()->getId()] ?? [];

            foreach ($additionalServices as $additionalService) {
                if (!in_array($additionalService->getId(), $offeredAdditionalServiceIds, true)) {
                    continue;
                }

                $this->addAdditionalServiceOrderItemData(
                    $orderData,
                    $productOrderItemData,
                    $additionalService,
                    $quantifiedProduct,
                    $orderProcessingData->getDomainConfig(),
                );
            }
        }

        return $orderProcessingStack->processNext($orderProcessingData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return array<int, int[]>
     */
    protected function getOfferedAdditionalServiceIdsByProductId(array $quantifiedProducts, int $domainId): array
    {
        $productIdsWithChosenAdditionalServices = [];

        foreach ($quantifiedProducts as $quantifiedProduct) {
            if ($quantifiedProduct->getAdditionalData(QuantifiedProduct::CART_ITEM_TYPE_KEY) !== CartItemTypeEnum::TYPE_PRODUCT) {
                continue;
            }

            if (($quantifiedProduct->getAdditionalData(QuantifiedProduct::ADDITIONAL_SERVICES_KEY) ?? []) === []) {
                continue;
            }

            $productIdsWithChosenAdditionalServices[] = $quantifiedProduct->getProduct()->getId();
        }

        if ($productIdsWithChosenAdditionalServices === []) {
            return [];
        }

        $offeredAdditionalServicesByProductId = $this->additionalServiceFacade->getEnabledIndexedByProductIds(
            $productIdsWithChosenAdditionalServices,
            $domainId,
        );

        $offeredAdditionalServiceIdsByProductId = [];

        foreach ($offeredAdditionalServicesByProductId as $productId => $offeredAdditionalServices) {
            $offeredAdditionalServiceIdsByProductId[$productId] = array_map(
                static fn (AdditionalService $additionalService) => $additionalService->getId(),
                $offeredAdditionalServices,
            );
        }

        return $offeredAdditionalServiceIdsByProductId;
    }

    protected function belongsOrderItemDataToQuantifiedProduct(
        ?OrderItemData $productOrderItemData,
        QuantifiedProduct $quantifiedProduct,
    ): bool {
        if ($productOrderItemData === null) {
            return false;
        }

        return $productOrderItemData->product?->getId() === $quantifiedProduct->getProduct()->getId();
    }

    protected function addAdditionalServiceOrderItemData(
        OrderData $orderData,
        OrderItemData $productOrderItemData,
        AdditionalService $additionalService,
        QuantifiedProduct $quantifiedProduct,
        DomainConfig $domainConfig,
    ): void {
        $domainId = $domainConfig->getId();
        $product = $quantifiedProduct->getProduct();
        $quantity = $quantifiedProduct->getQuantity();

        $unitPrice = $this->additionalServicePriceCalculation->calculatePrice($additionalService, $product, $domainId);
        $totalPrice = $this->additionalServicePriceCalculation->calculateTotalPrice(
            $additionalService,
            $product,
            $domainId,
            $quantity,
        );

        $orderItemData = $this->orderItemDataFactory->create(OrderItemTypeEnum::TYPE_ADDITIONAL_SERVICE);
        $orderItemData->name = $additionalService->getName($domainConfig->getLocale()) ?? $additionalService->getCatnum();
        $orderItemData->catnum = $additionalService->getCatnum();
        $orderItemData->quantity = $quantity;
        $orderItemData->unitName = $productOrderItemData->unitName;
        $orderItemData->vatPercent = $this->additionalServicePriceCalculation->getVat($additionalService, $product, $domainId)->getPercent();
        $orderItemData->setUnitPrice($unitPrice);
        $orderItemData->setTotalPrice($totalPrice);
        $orderItemData->additionalService = $additionalService;

        $productOrderItemData->relatedOrderItemsData[] = $orderItemData;

        $orderData->addItem($orderItemData);
        $orderData->addTotalPrice($totalPrice, OrderItemTypeEnum::TYPE_ADDITIONAL_SERVICE);
    }
}
