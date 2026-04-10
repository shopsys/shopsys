<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'payment_domains')]
#[ORM\UniqueConstraint(name: 'payment_domain', columns: ['payment_id', 'domain_id'])]
#[ORM\Entity]
class PaymentDomain
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Payment::class, inversedBy: 'domains')]
    protected $payment;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $enabled;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Vat::class)]
    protected $vat;

    /**
     * @var \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: GoPayPaymentMethod::class)]
    protected $goPayPaymentMethod;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $hiddenByGoPay;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    protected $accountNumber;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    protected $iban;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    protected $bicSwift;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 20)]
    protected $orderRoundingType;

    public function __construct(
        Payment $payment,
        int $domainId,
        Vat $vat,
        ?GoPayPaymentMethod $goPayPaymentMethod = null,
        bool $hiddenByGoPay = false,
        ?string $accountNumber = null,
        ?string $iban = null,
        ?string $bic = null,
        string $orderRoundingType = OrderRoundingTypeEnum::NONE,
    ) {
        $this->payment = $payment;
        $this->domainId = $domainId;
        $this->vat = $vat;
        $this->enabled = true;
        $this->goPayPaymentMethod = $goPayPaymentMethod;
        $this->hiddenByGoPay = $hiddenByGoPay;
        $this->accountNumber = $accountNumber;
        $this->iban = $iban;
        $this->bicSwift = $bic;
        $this->orderRoundingType = $orderRoundingType;
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

    public function setEnabled(bool $enabled): void
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

    /**
     * @return string|null
     */
    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    /**
     * @param string|null $accountNumber
     */
    public function setAccountNumber($accountNumber): void
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return string|null
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param string|null $iban
     */
    public function setIban($iban): void
    {
        $this->iban = $iban;
    }

    /**
     * @return string|null
     */
    public function getBicSwift()
    {
        return $this->bicSwift;
    }

    /**
     * @param string|null $bicSwift
     */
    public function setBicSwift($bicSwift): void
    {
        $this->bicSwift = $bicSwift;
    }

    /**
     * @return string
     */
    public function getOrderRoundingType()
    {
        return $this->orderRoundingType;
    }

    /**
     * @param string $orderRoundingType
     */
    public function setOrderRoundingType($orderRoundingType): void
    {
        $this->orderRoundingType = $orderRoundingType;
    }
}
