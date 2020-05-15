<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

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
 */
class PromoCode extends BasePromoCode
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     *
     * @ORM\Column(type="text",unique=false)
     */
    protected $code;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $datetimeValidFrom;

    /**
     * @var \DateTime|null
     *
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
     *
     * @ORM\Column(type="text")
     */
    protected $identifier;

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
    public function __construct(PromoCodeData $promoCodeData)
    {
        parent::__construct($promoCodeData);
        $this->domainId = $promoCodeData->domainId;
        $this->datetimeValidFrom = $promoCodeData->datetimeValidFrom;
        $this->datetimeValidTo = $promoCodeData->datetimeValidTo;
        $this->remainingUses = $promoCodeData->remainingUses;
        $this->identifier = $promoCodeData->identifier;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
    public function edit(PromoCodeData $promoCodeData): void
    {
        parent::edit($promoCodeData);
        $this->domainId = $promoCodeData->domainId;
        $this->datetimeValidFrom = $promoCodeData->datetimeValidFrom;
        $this->datetimeValidTo = $promoCodeData->datetimeValidTo;
        $this->remainingUses = $promoCodeData->remainingUses;
        $this->identifier = $promoCodeData->identifier;
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
    public function getDatetimeValidFrom(): ?\DateTime
    {
        return $this->datetimeValidFrom;
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetimeValidTo(): ?\DateTime
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
}
