<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *      name="gopay_payment_methods",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="gopay_payment_method_unique", columns={"domain_id", "identifier"})
 *      }
 * )
 * @ORM\Entity
 */
class GoPayPaymentMethod
{
    public const IDENTIFIER_PAYMENT_CARD = 'PAYMENT_CARD';
    public const IDENTIFIER_BANK_TRANSFER = 'BANK_ACCOUNT';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    protected $identifier;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    protected $name;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency")
     * @ORM\JoinColumn(nullable=false, name="currency_id", referencedColumnName="id")
     */
    protected $currency;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $imageNormalUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    protected $imageLargeUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    protected $paymentGroup;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $available;

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $paymentMethodData
     */
    public function __construct(GoPayPaymentMethodData $paymentMethodData)
    {
        $this->identifier = $paymentMethodData->identifier;
        $this->currency = $paymentMethodData->currency;

        $this->fillCommonFields($paymentMethodData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $goPayPaymentMethodData
     */
    public function edit(GoPayPaymentMethodData $goPayPaymentMethodData): void
    {
        $this->fillCommonFields($goPayPaymentMethodData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $goPayPaymentMethodData
     */
    public function fillCommonFields(GoPayPaymentMethodData $goPayPaymentMethodData): void
    {
        $this->name = $goPayPaymentMethodData->name;
        $this->imageNormalUrl = $goPayPaymentMethodData->imageNormalUrl;
        $this->imageLargeUrl = $goPayPaymentMethodData->imageLargeUrl;
        $this->paymentGroup = $goPayPaymentMethodData->paymentGroup;
        $this->domainId = $goPayPaymentMethodData->domainId;
        $this->available = $goPayPaymentMethodData->available;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getImageNormalUrl()
    {
        return $this->imageNormalUrl;
    }

    /**
     * @return string
     */
    public function getImageLargeUrl()
    {
        return $this->imageLargeUrl;
    }

    /**
     * @return string
     */
    public function getPaymentGroup()
    {
        return $this->paymentGroup;
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
    public function isAvailable()
    {
        return $this->available;
    }

    public function setUnavailable()
    {
        $this->available = false;
    }
}
