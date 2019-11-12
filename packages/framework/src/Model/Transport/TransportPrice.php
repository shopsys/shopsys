<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

/**
 * @ORM\Table(name="transport_prices")
 * @ORM\Entity
 */
class TransportPrice
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Transport\Transport", inversedBy="prices")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $transport;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $currency;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     *
     * @ORM\Column(type="money", precision=20, scale=6)
     */
    protected $price;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     *
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $vat;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param int $domainId
     */
    public function __construct(Transport $transport, Currency $currency, Money $price, Vat $vat, int $domainId)
    {
        $this->transport = $transport;
        $this->currency = $currency;
        $this->price = $price;
        $this->vat = $vat;
        $this->domainId = $domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    public function getTransport(): Transport
    {
        return $this->transport;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPrice(): Money
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
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     */
    public function setVat(Vat $vat): void
    {
        $this->vat = $vat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getVat(): Vat
    {
        return $this->vat;
    }
}
