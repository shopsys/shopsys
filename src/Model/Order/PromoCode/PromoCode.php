<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use App\Model\Product\Parameter\Exception\DeprecatedParameterPropertyException;
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
 * @method setData(\App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 */
class PromoCode extends BasePromoCode
{
    public const MASS_GENERATED_CODE_LENGTH = 6;

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
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $massGenerate;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $prefix;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $applyOnSecondProduct;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $onSale;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $inAction;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $scontoPrice;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $withoutLowPrice;

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
        $this->massGenerate = $promoCodeData->massGenerate;
        $this->prefix = $promoCodeData->prefix;
        $this->applyOnSecondProduct = $promoCodeData->applyOnSecondProduct;
        $this->onSale = $promoCodeData->onSale;
        $this->inAction = $promoCodeData->inAction;
        $this->scontoPrice = $promoCodeData->scontoPrice;
        $this->withoutLowPrice = $promoCodeData->withoutLowPrice;
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
        $this->massGenerate = $promoCodeData->massGenerate;
        $this->prefix = $promoCodeData->prefix;
        $this->applyOnSecondProduct = $promoCodeData->applyOnSecondProduct;
        $this->onSale = $promoCodeData->onSale;
        $this->inAction = $promoCodeData->inAction;
        $this->scontoPrice = $promoCodeData->scontoPrice;
        $this->withoutLowPrice = $promoCodeData->withoutLowPrice;
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
     * @return bool
     */
    public function isApplyOnSecondProduct(): bool
    {
        return $this->applyOnSecondProduct;
    }

    /**
     * @return bool
     */
    public function isOnSale(): bool
    {
        return $this->onSale;
    }

    /**
     * @return bool
     */
    public function isInAction(): bool
    {
        return $this->inAction;
    }

    /**
     * @return bool
     */
    public function isScontoPrice(): bool
    {
        return $this->scontoPrice;
    }

    /**
     * @return bool
     */
    public function isWithoutLowPrice(): bool
    {
        return $this->withoutLowPrice;
    }

    /**
     * @deprecated
     * @throws \App\Model\Product\Parameter\Exception\DeprecatedParameterPropertyException
     * @return string
     */
    public function getPercent(): string
    {
        throw new DeprecatedParameterPropertyException('percent');
    }
}
