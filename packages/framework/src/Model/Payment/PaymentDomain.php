<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

/**
 * @ORM\Table(
 *     name="payment_domains",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="payment_domain", columns={"payment_id", "domain_id"})
 *     }
 * )
 * @ORM\Entity
 */
class PaymentDomain
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Payment\Payment", inversedBy="domains")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    protected $payment;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $enabled;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $vat;

    /**
     * @var \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $goPayPaymentMethod;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $hiddenByGoPay;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment $payment
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null $goPayPaymentMethod
     * @param bool $hiddenByGoPay
     */
    public function __construct(
        Payment $payment,
        int $domainId,
        Vat $vat,
        ?GoPayPaymentMethod $goPayPaymentMethod = null,
        bool $hiddenByGoPay = false,
    ) {
        $this->payment = $payment;
        $this->domainId = $domainId;
        $this->vat = $vat;
        $this->enabled = true;
        $this->goPayPaymentMethod = $goPayPaymentMethod;
        $this->hiddenByGoPay = $hiddenByGoPay;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getVat()
    {
        return $this->vat;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     */
    public function setVat($vat): void
    {
        $this->vat = $vat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null
     */
    public function getGoPayPaymentMethod()
    {
        return $this->goPayPaymentMethod;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null $goPayPaymentMethod
     */
    public function setGoPayPaymentMethod($goPayPaymentMethod): void
    {
        $this->goPayPaymentMethod = $goPayPaymentMethod;
    }

    /**
     * @param bool $state
     */
    public function setHiddenByGoPay(bool $state): void
    {
        $this->hiddenByGoPay = $state;
    }

    /**
     * @return bool
     */
    public function isHiddenByGoPay()
    {
        return $this->hiddenByGoPay;
    }
}
