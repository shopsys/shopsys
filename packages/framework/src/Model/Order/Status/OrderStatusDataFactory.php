<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class OrderStatusDataFactory
{
    public function __construct(protected readonly Domain $domain)
    {
    }

    protected function createInstance(): OrderStatusData
    {
        return new OrderStatusData();
    }

    public function create(): OrderStatusData
    {
        $orderStatusData = $this->createInstance();
        $this->fillNew($orderStatusData);

        return $orderStatusData;
    }

    protected function fillNew(OrderStatusData $orderStatusData): void
    {
        foreach ($this->domain->getAllLocales() as $locale) {
            $orderStatusData->name[$locale] = null;
        }
    }

    public function createFromOrderStatus(OrderStatus $orderStatus): OrderStatusData
    {
        $orderStatusData = $this->createInstance();
        $this->fillFromOrderStatus($orderStatusData, $orderStatus);

        return $orderStatusData;
    }

    protected function fillFromOrderStatus(OrderStatusData $orderStatusData, OrderStatus $orderStatus): void
    {
        $translations = $orderStatus->getTranslations();
        $names = [];

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $orderStatusData->name = $names;
        $orderStatusData->productReviewsAllowed = $orderStatus->areProductReviewsAllowed();
    }
}
