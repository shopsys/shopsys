<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\TransportAndPayment;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'free_transport_and_payment_price_limits')]
#[ORM\Entity]
class FreeTransportAndPaymentPriceLimit
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    protected $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'currency_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Currency::class)]
    protected $currency;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'money', precision: 20, scale: 6)]
    protected $price;

    public function __construct(int $domainId, Currency $currency, Money $price)
    {
        $this->domainId = $domainId;
        $this->currency = $currency;
        $this->price = $price;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
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
}
