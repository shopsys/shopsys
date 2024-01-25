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
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport
     * @ORM\Id
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
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $domainId
     */
    public function __construct(Transport $transport, Money $price, int $domainId)
    {
        $this->transport = $transport;
        $this->price = $price;
        $this->domainId = $domainId;
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
    public function setPrice(Money $price): void
    {
        $this->price = $price;
    }

    /**
     * @param int $domainId
     */
    public function setDomainId(int $domainId): void
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
}
