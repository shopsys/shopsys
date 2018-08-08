<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

/**
 * @ORM\Table(name="payment_prices")
 * @ORM\Entity
 */
class PaymentPrice
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Payment", inversedBy="prices")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $payment;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $currency;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=20, scale=6)
     */
    protected $price;
    
    public function __construct(Payment $payment, Currency $currency, string $price)
    {
        $this->payment = $payment;
        $this->currency = $currency;
        $this->price = $price;
    }

    public function getCurrency(): \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
    {
        return $this->currency;
    }

    public function getPayment(): \Shopsys\FrameworkBundle\Model\Payment\Payment
    {
        return $this->payment;
    }

    public function getPrice(): string
    {
        return $this->price;
    }
    
    public function setPrice(string $price): void
    {
        $this->price = $price;
    }
}
