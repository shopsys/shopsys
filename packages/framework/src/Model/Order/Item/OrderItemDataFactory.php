<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\Exception\OrderItemUnitPricesAreInconsistentButTotalsAreNotForcedException;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;

class OrderItemDataFactory
{
    public function __construct(
        protected readonly OrderItemPriceCalculation $orderItemPriceCalculation,
        protected readonly PricingSetting $pricingSetting,
    ) {
    }

    protected function createInstance(): OrderItemData
    {
        return new OrderItemData();
    }

    public function create(string $orderItemType): OrderItemData
    {
        $orderItemData = $this->createInstance();

        $orderItemData->type = $orderItemType;

        return $orderItemData;
    }

    public function createFromOrderItem(OrderItem $orderItem): OrderItemData
    {
        $orderItemData = $this->createInstance();
        $this->fillFromOrderItem($orderItemData, $orderItem);
        $this->addFieldsByOrderItemType($orderItemData, $orderItem);

        return $orderItemData;
    }

    protected function fillFromOrderItem(OrderItemData $orderItemData, OrderItem $orderItem)
    {
        $orderItemData->name = $orderItem->getName();
        $orderItemData->unitPriceWithVat = $orderItem->getUnitPriceWithVat();
        $orderItemData->unitPriceWithoutVat = $orderItem->getUnitPriceWithoutVat();

        $orderItemTotalPrice = $this->orderItemPriceCalculation->calculateTotalPrice($orderItem);
        $orderItemData->totalPriceWithVat = $orderItemTotalPrice->getPriceWithVat();
        $orderItemData->totalPriceWithoutVat = $orderItemTotalPrice->getPriceWithoutVat();

        $orderItemData->vatPercent = $orderItem->getVatPercent();
        $orderItemData->quantity = $orderItem->getQuantity();
        $orderItemData->unitName = $orderItem->getUnitName();
        $orderItemData->catnum = $orderItem->getCatnum();
        $orderItemData->type = $orderItem->getType();

        $orderItemData->usePriceCalculation = $this->isUsingPriceCalculation($orderItemData, $orderItem);
    }

    protected function addFieldsByOrderItemType(OrderItemData $orderItemData, OrderItem $orderItem): void
    {
        if ($orderItem->isTypeTransport()) {
            $orderItemData->transport = $orderItem->getTransport();
        } elseif ($orderItem->isTypePayment()) {
            $orderItemData->payment = $orderItem->getPayment();
        } elseif ($orderItem->isTypeProduct()) {
            $orderItemData->product = $orderItem->getProduct();
        } elseif ($orderItem->isTypeProductGift()) {
            $orderItemData->product = $orderItem->getProductGift();
        }
    }

    protected function isUsingPriceCalculation(OrderItemData $orderItemData, OrderItem $orderItem): bool
    {
        if ($orderItem->hasForcedTotalPrice()) {
            return false;
        }

        switch ($this->pricingSetting->getInputPriceType()) {
            case PricingSetting::PRICE_TYPE_WITH_VAT:
                $calculatedPriceWithoutVat = $this->orderItemPriceCalculation->calculatePriceWithoutVatForInputPriceWithVat(
                    $orderItemData,
                    $orderItem->getOrder()->getDomainId(),
                );

                if (!$orderItemData->unitPriceWithoutVat->equals($calculatedPriceWithoutVat)) {
                    throw new OrderItemUnitPricesAreInconsistentButTotalsAreNotForcedException(
                        $orderItem,
                        $calculatedPriceWithoutVat,
                    );
                }

                break;
            case PricingSetting::PRICE_TYPE_WITHOUT_VAT:
                $calculatedPriceWithVat = $this->orderItemPriceCalculation->calculatePriceWithVatForInputPriceWithoutVat(
                    $orderItemData,
                    $orderItem->getOrder()->getDomainId(),
                    $orderItem->getOrder()->getCurrency(),
                );

                if (!$orderItemData->unitPriceWithVat->equals($calculatedPriceWithVat)) {
                    throw new OrderItemUnitPricesAreInconsistentButTotalsAreNotForcedException(
                        $orderItem,
                        $calculatedPriceWithVat,
                    );
                }

                break;
            default:
                throw new InvalidInputPriceTypeException();
        }

        return true;
    }

    public function createFromQuantifiedProduct(
        QuantifiedProduct $quantifiedProduct,
        QuantifiedItemPriceInterface $quantifiedItemPrice,
        string $locale,
    ): OrderItemData {
        $product = $quantifiedProduct->getProduct();

        $orderItemData = $this->create($quantifiedProduct->getAdditionalData(QuantifiedProduct::CART_ITEM_TYPE_KEY));
        $orderItemData->name = $product->getFullName($locale);
        $orderItemData->setUnitPrice($quantifiedItemPrice->getUnitPrice());
        $orderItemData->setTotalPrice($quantifiedItemPrice->getTotalPrice());
        $orderItemData->vatPercent = $quantifiedItemPrice->getVat()->getPercent();
        $orderItemData->quantity = $quantifiedProduct->getQuantity();
        $orderItemData->unitName = $product->getUnit()->getName($locale);
        $orderItemData->catnum = $product->getCatnum();
        $orderItemData->product = $product;

        return $orderItemData;
    }
}
