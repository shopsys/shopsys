<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\ReturnHash;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable(exposed: false)]
#[ORM\Table(name: 'payment_return_hashes')]
#[ORM\Entity]
class PaymentReturnHash
{
    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 64)]
    #[ORM\Id]
    protected $hash;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Order
     */
    #[ORM\JoinColumn(nullable: false, name: 'order_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Order::class)]
    protected $order;

    /**
     * @var \DateTimeImmutable
     */
    #[ORM\Column(type: 'datetime_immutable')]
    protected $expiresAt;

    /**
     * @param string $hash
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @param \DateTimeImmutable $expiresAt
     */
    public function __construct($hash, $order, $expiresAt)
    {
        $this->hash = $hash;
        $this->order = $order;
        $this->expiresAt = $expiresAt;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }
}
