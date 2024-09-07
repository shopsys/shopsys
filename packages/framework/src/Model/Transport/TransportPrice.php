<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;

/**
 * @ORM\Table(name="transport_prices")
 * @ORM\Entity
 */
class TransportPrice
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Transport\Transport", inversedBy="prices")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $transport;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected $price;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var int
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $maxWeight;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $domainId
     * @param int|null $maxWeight
     */
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
