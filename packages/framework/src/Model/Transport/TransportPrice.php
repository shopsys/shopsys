<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\EntityLogIdentify;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableChild;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableParentProperty;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[LoggableChild]
#[ORM\Table(name: 'transport_prices')]
#[ORM\UniqueConstraint(name: 'unique_weight_limit_on_domain', columns: ['max_weight', 'domain_id', 'transport_id'])]
#[ORM\Entity]
class TransportPrice
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    #[AsMcpColumn]
    #[LoggableParentProperty]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Transport::class, inversedBy: 'prices')]
    protected $transport;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'money', precision: 20, scale: 6)]
    protected $price;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $maxWeight;

    public function __construct(Transport $transport, Money $price, int $domainId, ?int $maxWeight)
    {
        $this->transport = $transport;
        $this->price = $price;
        $this->domainId = $domainId;
        $this->maxWeight = $maxWeight;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    public function getTransport()
    {
        return $this->transport;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    /**
     * @param int $domainId
     */
    public function setDomainId($domainId): void
    {
        $this->domainId = $domainId;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * The price is part of the label, because a record of a created price does not hold its values
     */
    #[EntityLogIdentify]
    public function getEntityLogIdentifier(): string
    {
        $price = $this->price->round(2)->getAmount();

        if ($this->maxWeight === null) {
            return sprintf('%d: %s', $this->domainId, $price);
        }

        return sprintf('%d, ≤ %d: %s', $this->domainId, $this->maxWeight, $price);
    }

    /**
     * @return int|null
     */
    public function getMaxWeight()
    {
        return $this->maxWeight;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
