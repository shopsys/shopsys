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
 * @method __construct(\App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 * @method edit(\App\Model\Order\PromoCode\PromoCodeData $promoCodeData)
 */
class PromoCode extends BasePromoCode
{
    public const MASS_GENERATED_CODE_LENGTH = 6;

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
     * @param \App\Model\Order\PromoCode\PromoCodeData $promoCodeData
     */
    protected function setData(PromoCodeData $promoCodeData): void
    {
        parent::setData($promoCodeData);

        $this->massGenerate = $promoCodeData->massGenerate;
        $this->prefix = $promoCodeData->prefix;
        $this->massGenerateBatchId = $promoCodeData->massGenerateBatchId;
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
}
