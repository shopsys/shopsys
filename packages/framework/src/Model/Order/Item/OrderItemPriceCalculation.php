<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\OrderItemHasNoIdException;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFactory;

class OrderItemPriceCalculation
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation $priceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFactory $vatFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatDataFactory $vatDataFactory
     */
    public function __construct(
        protected readonly PriceCalculation $priceCalculation,
        protected readonly VatFactory $vatFactory,
        protected readonly VatDataFactory $vatDataFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData $orderItemData
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function calculatePriceWithoutVat(OrderItemData $orderItemData, int $domainId): Money
    {
        $vatData = $this->vatDataFactory->create();
        $vatData->name = 'orderItemVat';
        $vatData->percent = $orderItemData->vatPercent;
        $vat = $this->vatFactory->create($vatData, $domainId);
        $vatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($orderItemData->unitPriceWithVat, $vat);

        return $orderItemData->unitPriceWithVat->subtract($vatAmount);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem $orderItem
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function calculateTotalPrice(OrderItem $orderItem): PriceInterface
    {
        if ($orderItem->hasForcedTotalPrice()) {
            return new Price($orderItem->getTotalPriceWithoutVat(), $orderItem->getTotalPriceWithVat());
        }

        $vatData = $this->vatDataFactory->create();
        $vatData->name = 'orderItemVat';
        $vatData->percent = $orderItem->getVatPercent();
        $vat = $this->vatFactory->create($vatData, $orderItem->getOrder()->getDomainId());

        $totalPriceWithVat = $orderItem->getUnitPriceWithVat()->multiply($orderItem->getQuantity());
        $totalVatAmount = $this->priceCalculation->getVatAmountByPriceWithVat($totalPriceWithVat, $vat);
        $totalPriceWithoutVat = $totalPriceWithVat->subtract($totalVatAmount);

        return new Price($totalPriceWithoutVat, $totalPriceWithVat);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[] $orderItems
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface[]
     */
    public function calculateTotalPricesIndexedById($orderItems): array
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
