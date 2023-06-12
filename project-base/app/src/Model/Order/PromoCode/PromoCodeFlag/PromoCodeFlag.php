<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode\PromoCodeFlag;

use App\Model\Order\PromoCode\PromoCode;
use App\Model\Product\Flag\Flag;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="promo_code_flags")
 * @ORM\Entity
 */
class PromoCodeFlag
{
    public const TYPE_INCLUSIVE = 'with';
    public const TYPE_EXCLUSIVE = 'without';

    /**
     * @var \App\Model\Order\PromoCode\PromoCode
     * @ORM\ManyToOne(targetEntity="App\Model\Order\PromoCode\PromoCode")
     * @ORM\JoinColumn(nullable=false, name="promo_code_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    private PromoCode $promoCode;

    /**
     * @var \App\Model\Product\Flag\Flag
     * @ORM\ManyToOne(targetEntity="App\Model\Product\Flag\Flag")
     * @ORM\JoinColumn(nullable=false, name="flag_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    private Flag $flag;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private string $type;

    /**
     * @param \App\Model\Product\Flag\Flag $flag
     * @param string $type
     */
    public function __construct(
        Flag $flag,
        string $type,
    ) {
        $this->flag = $flag;
        $this->type = $type;
    }

    /**
     * @return \App\Model\Product\Flag\Flag
     */
    public function getFlag(): Flag
    {
        return $this->flag;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isInclusive(): bool
    {
        return $this->type === self::TYPE_INCLUSIVE;
    }

    /**
     * @return bool
     */
    public function isExclusive(): bool
    {
        return $this->type === self::TYPE_EXCLUSIVE;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function setPromoCode(PromoCode $promoCode): void
    {
        $this->promoCode = $promoCode;
    }
}
