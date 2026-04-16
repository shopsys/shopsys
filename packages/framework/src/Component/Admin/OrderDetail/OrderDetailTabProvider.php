<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Admin\OrderDetail;

use Override;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('shopsys.order_detail_tab_provider')]
class OrderDetailTabProvider implements OrderDetailTabProviderInterface
{
    /**
     * @var array<string, \Shopsys\FrameworkBundle\Component\Admin\OrderDetail\OrderDetailTab>
     */
    protected array $tabs = [];

    /**
     * @return array<string, \Shopsys\FrameworkBundle\Component\Admin\OrderDetail\OrderDetailTab>
     */
    #[Override]
    public function getTabs(Order $order): array
    {
        $this->tabs = [];

        $this->addTab(new OrderDetailTab(
            'items',
            t('Items'),
            200,
            'package',
            '@ShopsysAdministration/content/order/tabs/items.html.twig',
        ));

        if ($order->getStatus()->getType() === OrderStatusTypeEnum::TYPE_WITHDRAWN) {
            $this->addTab(new OrderDetailTab(
                'withdrawal',
                t('Withdrawal'),
                100,
                'arrow-back-up',
                '@ShopsysAdministration/content/order/tabs/withdrawal.html.twig',
            ));
        }

        $this->addTab(new OrderDetailTab(
            'payments',
            t('Payments'),
            400,
            'credit-card',
            '@ShopsysAdministration/content/order/tabs/payments.html.twig',
        ));

        $this->addTab(new OrderDetailTab(
            'history',
            t('History'),
            500,
            'history',
            '@ShopsysAdministration/content/order/tabs/history.html.twig',
        ));

        return $this->tabs;
    }

    protected function addTab(OrderDetailTab $tab): void
    {
        $this->tabs[$tab->getIdentifier()] = $tab;
    }

    protected function removeTab(string $identifier): void
    {
        unset($this->tabs[$identifier]);
    }
}
