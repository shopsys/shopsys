<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="promo_codes",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="domain_code_unique", columns={
 *         "domain_id", "code"
 *     })}
 * )
 * @ORM\Entity
 */
class PromoCode
{
    public const int DISCOUNT_TYPE_PERCENT = 1;
    public const int DISCOUNT_TYPE_NOMINAL = 2;
    public const MASS_GENERATED_CODE_LENGTH = 6;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $code;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $discountType;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $registeredCustomerUserOnly;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

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
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $massGenerate;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $prefix;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $massGenerateBatchId;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
    public function __construct(PromoCodeData $promoCodeData)
    {
        $this->setData($promoCodeData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
    public function edit(PromoCodeData $promoCodeData): void
    {
        $this->setData($promoCodeData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
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
     * @return int
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetimeValidFrom()
    {
        return $this->datetimeValidFrom;
    }

    /**
     * @return \DateTime|null
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
}
