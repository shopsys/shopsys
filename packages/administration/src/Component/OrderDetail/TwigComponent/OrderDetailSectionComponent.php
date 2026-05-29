<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\OrderDetail\TwigComponent;

use Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailSection;
use Shopsys\AdministrationBundle\Component\OrderDetail\OrderDetailSectionRegistry;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(
    name: 'OrderDetail:Section',
    template: '@ShopsysAdministration/content/order/detail/components/section.html.twig',
)]
class OrderDetailSectionComponent
{
    public int $orderId;

    public string $sectionId;

    /**
     * @var array<string, mixed>
     */
    public array $context = [];

    protected ?Order $order = null;

    protected ?OrderDetailSection $section = null;

    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderDetailSectionRegistry $orderDetailSectionRegistry,
    ) {
    }

    public function mount(?Order $order = null): void
    {
        if ($order === null) {
            return;
        }

        $this->order = $order;
        $this->orderId = $order->getId();
    }

    public function getOrder(): Order
    {
        return $this->order ??= $this->orderFacade->getById($this->orderId);
    }

    public function getSection(): OrderDetailSection
    {
        return $this->section ??= $this->orderDetailSectionRegistry->getSection($this->sectionId);
    }
}
