<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class OrderStatusDataFactory implements OrderStatusDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(protected readonly Domain $domain)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData
     */
    protected function createInstance(): OrderStatusData
    {
        return new OrderStatusData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData
     */
    public function create(): OrderStatusData
    {
        $orderStatusData = $this->createInstance();
        $this->fillNew($orderStatusData);

        return $orderStatusData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData
     */
    protected function fillNew(OrderStatusData $orderStatusData): void
    {
        foreach ($this->domain->getAllLocales() as $locale) {
            $orderStatusData->name[$locale] = null;
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus $orderStatus
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData
     */
    public function createFromOrderStatus(OrderStatus $orderStatus): OrderStatusData
    {
        $orderStatusData = $this->createInstance();
        $this->fillFromOrderStatus($orderStatusData, $orderStatus);

        return $orderStatusData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus $orderStatus
     */
    protected function fillFromOrderStatus(OrderStatusData $orderStatusData, OrderStatus $orderStatus)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTranslation[] $translations */
        $translations = $orderStatus->getTranslations();
        $names = [];

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
        }
        $orderStatusData->name = $names;
    }
}
