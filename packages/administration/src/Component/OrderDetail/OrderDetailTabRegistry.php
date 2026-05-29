<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class OrderDetailTabRegistry
{
    /**
     * @param iterable<\Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailTabProviderInterface> $tabProviders
     */
    public function __construct(
        #[AutowireIterator('shopsys.order_detail_tab_provider')]
        protected readonly iterable $tabProviders,
    ) {
    }

    /**
     * @return array<string, \Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailTab>
     */
    public function getTabs(Order $order): array
    {
        $tabs = [];

        foreach ($this->tabProviders as $tabProvider) {
            foreach ($tabProvider->getTabs() as $tab) {
                if ($tab->isVisible($order)) {
                    $tabs[$tab->getId()] = $tab;
                }
            }
        }

        uasort(
            $tabs,
            static fn (OrderDetailTab $firstTab, OrderDetailTab $secondTab) => $firstTab->getPosition() <=> $secondTab->getPosition(),
        );

        return $tabs;
    }

    public function findTabById(Order $order, string $tabId): ?OrderDetailTab
    {
        return $this->getTabs($order)[$tabId] ?? null;
    }
}
