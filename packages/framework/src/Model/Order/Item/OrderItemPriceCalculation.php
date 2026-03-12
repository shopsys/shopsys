<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\OrderItemHasNoIdException;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidInputPriceTypeException;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFactory;

class OrderItemPriceCalculation
{
    public function __construct(
        protected readonly PriceCalculation $priceCalculation,
        protected readonly VatFactory $vatFactory,
        protected readonly VatDataFactory $vatDataFactory,
        protected readonly PricingSetting $pricingSetting,
        protected readonly Rounding $rounding,
    ) {
    }

    public function calculatePriceWithoutVatForInputPriceWithVat(
        OrderItemData $orderItemData,
        int $domainId,
        int $roundingPlaces,
    ): Money {
        $vatData = $this->vatDataFactory->create();
        $vatData->name = 'orderItemVat';
        $vatData->percent = $orderItemData->vatPercent;
        $vat = $this->vatFactory->create($vatData, $domainId);
        $vatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($orderItemData->unitPriceWithVat, $vat, $roundingPlaces);

        return $orderItemData->unitPriceWithVat->subtract($vatAmount);
    }

    public function calculatePriceWithVatForInputPriceWithoutVat(
        OrderItemData $orderItemData,
        int $domainId,
        string $currencyRoundingType,
    ): Money {
        $vatData = $this->vatDataFactory->create();
        $vatData->name = 'orderItemVat';
        $vatData->percent = $orderItemData->vatPercent;
        $vat = $this->vatFactory->create($vatData, $domainId);

        return $this->rounding->roundPriceWithVat($this->priceCalculation->applyVatPercent($orderItemData->unitPriceWithoutVat, $vat), $currencyRoundingType);
    }

    public function calculateTotalPrice(OrderItem $orderItem): PriceInterface
    {
        if ($orderItem->hasForcedTotalPrice()) {
            return new Price($orderItem->getTotalPriceWithoutVat(), $orderItem->getTotalPriceWithVat());
        }

        $vatData = $this->vatDataFactory->create();
        $vatData->name = 'orderItemVat';
        $vatData->percent = $orderItem->getVatPercent();
        $order = $orderItem->getOrder();
        $vat = $this->vatFactory->create($vatData, $order->getDomainId());
        $roundingType = $order->getCurrencyRoundingType();
        $roundingPlaces = $order->getCurrencyRoundingPlacesPriceWithoutVat();

        switch ($this->pricingSetting->getInputPriceType()) {
            case PricingSetting::PRICE_TYPE_WITH_VAT:
                $totalPriceWithVat = $orderItem->getUnitPriceWithVat()->multiply($orderItem->getQuantity());
                $totalVatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($totalPriceWithVat, $vat, $roundingPlaces);
                $totalPriceWithoutVat = $totalPriceWithVat->subtract($totalVatAmount);

                return new Price($totalPriceWithoutVat, $totalPriceWithVat);

            case PricingSetting::PRICE_TYPE_WITHOUT_VAT:
                $totalPriceWithoutVat = $this->rounding->roundPriceWithoutVat(
                    $orderItem->getUnitPriceWithoutVat()->multiply($orderItem->getQuantity()),
                    $roundingPlaces,
                );

                $totalPriceWithVat = $this->rounding->roundPriceWithVat(
                    $orderItem->getUnitPriceWithVat()->multiply($orderItem->getQuantity()),
                    $roundingType,
                );

                return new Price($totalPriceWithoutVat, $totalPriceWithVat);

            default:
                throw new InvalidInputPriceTypeException();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[] $orderItems
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface[]
     */
    public function calculateTotalPricesIndexedById(array $orderItems): array
    {
        $prices = [];

        foreach ($orderItems as $orderItem) {
            if ($orderItem->getId() === null) {
                $message = 'OrderItem must have ID filled';

                throw new OrderItemHasNoIdException($message);
            }
            $prices[$orderItem->getId()] = $this->calculateTotalPrice($orderItem);
        }

        return $prices;
    }
}
