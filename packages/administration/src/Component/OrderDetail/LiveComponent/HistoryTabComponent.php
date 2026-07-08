<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail\LiveComponent;

use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(
    name: self::COMPONENT_NAME,
    template: '@ShopsysAdministration/content/order/detail/components/history_tab.html.twig',
)]
class HistoryTabComponent
{
    use DefaultActionTrait;

    public const string COMPONENT_NAME = 'OrderDetail:HistoryTab';

    #[LiveProp]
    public int $orderId;

    public function __construct(
        protected readonly EntityLogFacade $entityLogFacade,
    ) {
    }

    public function getEntityName(): string
    {
        return $this->entityLogFacade->getEntityNameByEntity(Order::class);
    }
}
