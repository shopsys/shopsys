<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Entity\DomainSeparatedEntityInterface;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'promo_codes')]
#[ORM\UniqueConstraint(name: 'domain_code_unique', columns: ['domain_id', 'code'])]
#[ORM\Entity]
class PromoCode implements DomainSeparatedEntityInterface
{
    public const int MASS_GENERATED_CODE_LENGTH = 6;

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
    #[ORM\Column(type: 'text')]
    protected $code;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 25)]
    protected $discountType;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $registeredCustomerUserOnly;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $datetimeValidFrom;

    /**
     * @var \DateTimeImmutable|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    protected $datetimeValidTo;

    /**
     * @var int|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $remainingUses;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $massGenerate;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', nullable: true)]
    protected $prefix;

    /**
     * @var int|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $massGenerateBatchId;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $enabled;

    public function __construct(PromoCodeData $promoCodeData)
    {
        $this->setData($promoCodeData);
    }

    public function edit(PromoCodeData $promoCodeData): void
    {
        $this->setData($promoCodeData);
    }

    protected function setData(PromoCodeData $promoCodeData): void
    {
        $this->code = $promoCodeData->code;
        $this->discountType = $promoCodeData->discountType;
        $this->domainId = $promoCodeData->domainId;
        $this->datetimeValidFrom = $promoCodeData->datetimeValidFrom;
        $this->datetimeValidTo = $promoCodeData->datetimeValidTo;
        $this->remainingUses = $promoCodeData->remainingUses;
        $this->registeredCustomerUserOnly = $promoCodeData->registeredCustomerUserOnly;
        $this->massGenerate = $promoCodeData->massGenerate;
        $this->prefix = $promoCodeData->prefix;
        $this->massGenerateBatchId = $promoCodeData->massGenerateBatchId;
        $this->enabled = $promoCodeData->enabled;
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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * @return int
     */
    #[Override]
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDatetimeValidFrom()
    {
        return $this->datetimeValidFrom;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getDatetimeValidTo()
    {
        return $this->datetimeValidTo;
    }

    /**
     * @return int|null
     */
    public function getRemainingUses()
    {
        return $this->remainingUses;
    }

    public function decreaseRemainingUses(): void
    {
        if ($this->remainingUses !== null && $this->remainingUses > 0) {
            $this->remainingUses--;
        }
    }

    /**
     * @return bool
     */
    public function isRegisteredCustomerUserOnly()
    {
        return $this->registeredCustomerUserOnly;
    }

    /**
     * @return bool
     */
    public function isMassGenerate()
    {
        return $this->massGenerate;
    }

    /**
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    public function isFreeTransportAndPaymentType(): bool
    {
        return $this->discountType === PromoCodeTypeEnum::DISCOUNT_TYPE_FREE_TRANSPORT_PAYMENT;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }
}
