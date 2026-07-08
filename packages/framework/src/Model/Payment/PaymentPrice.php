<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'payment_prices')]
#[ORM\Entity]
class PaymentPrice
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Payment::class, inversedBy: 'prices')]
    protected $payment;

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
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'currency_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Currency::class)]
    protected $currency;

    public function __construct(Payment $payment, Money $price, int $domainId, Currency $currency)
    {
        $this->payment = $payment;
        $this->price = $price;
        $this->domainId = $domainId;
        $this->currency = $currency;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function getPayment()
    {
        return $this->payment;
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
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @param int $domainId
     */
    public function setDomainId($domainId): void
    {
        $this->domainId = $domainId;
    }
}
