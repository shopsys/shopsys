<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Model\Product\Parameter\Exception\DeprecatedParameterPropertyException;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode as BasePromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData;

/**
 * @ORM\Table(name="promo_codes",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="domain_code_unique", columns={
 *         "domain_id", "code"
 *     })}
 * )
 * @ORM\Entity
 * @method __construct(\App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 * @method edit(\App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 */
class PromoCode extends BasePromoCode
{
    public const MASS_GENERATED_CODE_LENGTH = 6;
    public const DISCOUNT_TYPE_PERCENT = 1;
    public const DISCOUNT_TYPE_NOMINAL = 2;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     * @ORM\Column(type="text",unique=false)
     */
    protected $code;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $datetimeValidFrom;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $datetimeValidTo;

    /**
     * @var int|null
     * @ORM\Column(type="integer",nullable=true)
     */
    protected $remainingUses;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $identifier;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $massGenerate;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $prefix;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $massGenerateBatchId;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $discountType;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private bool $registeredCustomerUserOnly;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
    protected function setData(PromoCodeData $promoCodeData): void
    {
        parent::setData($promoCodeData);

        $this->domainId = $promoCodeData->domainId;
        $this->datetimeValidFrom = $promoCodeData->datetimeValidFrom;
        $this->datetimeValidTo = $promoCodeData->datetimeValidTo;
        $this->remainingUses = $promoCodeData->remainingUses;
        $this->identifier = $promoCodeData->identifier;
        $this->massGenerate = $promoCodeData->massGenerate;
        $this->prefix = $promoCodeData->prefix;
        $this->massGenerateBatchId = $promoCodeData->massGenerateBatchId;
        $this->discountType = $promoCodeData->discountType;
        $this->registeredCustomerUserOnly = $promoCodeData->registeredCustomerUserOnly;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetimeValidFrom(): ?DateTime
    {
        return $this->datetimeValidFrom;
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetimeValidTo(): ?DateTime
    {
        return $this->datetimeValidTo;
    }

    /**
     * @return int|null
     */
    public function getRemainingUses(): ?int
    {
        return $this->remainingUses;
    }

    public function decreaseRemainingUses(): void
    {
        if ($this->remainingUses !== null & $this->remainingUses > 0) {
            $this->remainingUses--;
        }
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return bool
     */
    public function isMassGenerate(): bool
    {
        return $this->massGenerate;
    }

    /**
     * @return string|null
     */
    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    /**
     * @deprecated original implementation is not used
     * @throws \App\Model\Product\Parameter\Exception\DeprecatedParameterPropertyException
     * @return string
     */
    public function getPercent(): string
    {
        throw new DeprecatedParameterPropertyException('percent');
    }

    /**
     * @return int
     */
    public function getDiscountType(): int
    {
        return $this->discountType;
    }

    /**
     * @return bool
     */
    public function isRegisteredCustomerUserOnly(): bool
    {
        return $this->registeredCustomerUserOnly;
    }
}
