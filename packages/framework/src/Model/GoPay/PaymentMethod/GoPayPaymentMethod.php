<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'gopay_payment_methods')]
#[ORM\UniqueConstraint(name: 'gopay_payment_method_unique', columns: ['domain_id', 'identifier', 'currency_id'])]
#[ORM\Entity]
class GoPayPaymentMethod
{
    public const IDENTIFIER_PAYMENT_CARD = 'PAYMENT_CARD';
    public const IDENTIFIER_BANK_TRANSFER = 'BANK_ACCOUNT';

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 20)]
    protected $identifier;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 50)]
    protected $name;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'currency_id', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Currency::class)]
    protected $currency;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $imageNormalUrl;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $imageLargeUrl;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 20)]
    protected $paymentGroup;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $available;

    public function __construct(GoPayPaymentMethodData $paymentMethodData)
    {
        $this->identifier = $paymentMethodData->identifier;
        $this->currency = $paymentMethodData->currency;

        $this->fillCommonFields($paymentMethodData);
    }

    public function edit(GoPayPaymentMethodData $goPayPaymentMethodData): void
    {
        $this->fillCommonFields($goPayPaymentMethodData);
    }

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

    public function setUnavailable(): void
    {
        $this->available = false;
    }
}
