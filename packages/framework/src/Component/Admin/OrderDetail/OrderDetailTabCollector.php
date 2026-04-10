<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Admin\OrderDetail;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class OrderDetailTabCollector
{
    /**
     * @param iterable<\Shopsys\FrameworkBundle\Component\Admin\OrderDetail\OrderDetailTabProviderInterface> $tabProviders
     */
    public function __construct(
        #[AutowireIterator('shopsys.order_detail_tab_provider')]
        protected readonly iterable $tabProviders,
    ) {
    }

    /**
     * @return array<string, \Shopsys\FrameworkBundle\Component\Admin\OrderDetail\OrderDetailTab>
     */
    public function getTabs(Order $order): array
    {
        $tabs = [];

        foreach ($this->tabProviders as $provider) {
            foreach ($provider->getTabs($order) as $identifier => $tab) {
                $tabs[$identifier] = $tab;
            }
        }

        uasort($tabs, fn (OrderDetailTab $a, OrderDetailTab $b) => $a->getPriority() <=> $b->getPriority());

        return $tabs;
    }
}
