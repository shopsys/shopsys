<?php

declare(strict_types=1);

namespace App\Model\GoPay\PaymentMethod;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

/**
 * @ORM\Table(
 *      name="gopay_payment_methods",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="gopay_payment_method_unique", columns={"currency_id", "identifier"})
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
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $identifier;

    /**
     * @var string
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency")
     * @ORM\JoinColumn(nullable=false, name="currency_id", referencedColumnName="id")
     */
    private $currency;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $imageNormalUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $imageLargeUrl;

    /**
     * @var string
     * @ORM\Column(type="string", length=20)
     */
    private $paymentGroup;

    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $paymentMethodData
     */
    public function __construct(GoPayPaymentMethodData $paymentMethodData)
    {
        $this->identifier = $paymentMethodData->identifier;
        $this->name = $paymentMethodData->name;
        $this->currency = $paymentMethodData->currency;
        $this->imageNormalUrl = $paymentMethodData->imageNormalUrl;
        $this->imageLargeUrl = $paymentMethodData->imageLargeUrl;
        $this->paymentGroup = $paymentMethodData->paymentGroup;
    }

    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodData $goPayPaymentMethodData
     */
    public function edit(GoPayPaymentMethodData $goPayPaymentMethodData): void
    {
        $this->name = $goPayPaymentMethodData->name;
        $this->imageNormalUrl = $goPayPaymentMethodData->imageNormalUrl;
        $this->imageLargeUrl = $goPayPaymentMethodData->imageLargeUrl;
        $this->paymentGroup = $goPayPaymentMethodData->paymentGroup;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function getImageNormalUrl(): string
    {
        return $this->imageNormalUrl;
    }

    /**
     * @return string
     */
    public function getImageLargeUrl(): string
    {
        return $this->imageLargeUrl;
    }

    /**
     * @return string
     */
    public function getPaymentGroup(): string
    {
        return $this->paymentGroup;
    }
}
