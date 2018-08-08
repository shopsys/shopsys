<?php

namespace Tests\ShopBundle\Database\EntityExtension\Model;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderTransportData;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

/**
 * @ORM\Entity
 *
 * Base of this class is a copy of OrderTransport entity from FrameworkBundle
 * Reason is described in /project-base/docs/wip_glassbox/entity-extension.md
 *
 * @see \Shopsys\FrameworkBundle\Model\Order\Item\OrderTransport
 */
class ExtendedOrderTransport extends ExtendedOrderItem
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Transport\Transport")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $transport;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $transportStringField;

    /**
     * @param string $name
     * @param string $vatPercent
     * @param int $quantity
     */
    public function __construct(
        Order $order,
        $name,
        Price $price,
        $vatPercent,
        $quantity,
        Transport $transport
    ) {
        parent::__construct(
            $order,
            $name,
            $price,
            $vatPercent,
            $quantity,
            null,
            null
        );
        $this->transport = $transport;
    }

    public function getTransport(): \Shopsys\FrameworkBundle\Model\Transport\Transport
    {
        return $this->transport;
    }

    public function edit(OrderItemData $orderTransportData)
    {
        if ($orderTransportData instanceof OrderTransportData) {
            $this->transport = $orderTransportData->transport;
            parent::edit($orderTransportData);
        } else {
            throw new \Shopsys\FrameworkBundle\Model\Order\Item\Exception\InvalidArgumentException(
                'Instance of ' . OrderTransportData::class . ' is required as argument.'
            );
        }
    }

    public function getTransportStringField(): ?string
    {
        return $this->transportStringField;
    }

    /**
     * @param string|null $transportStringField
     */
    public function setTransportStringField($transportStringField)
    {
        $this->transportStringField = $transportStringField;
    }
}
