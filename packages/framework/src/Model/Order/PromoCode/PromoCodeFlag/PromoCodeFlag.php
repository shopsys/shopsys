<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

/**
 * @ORM\Table(name="promo_code_flags")
 * @ORM\Entity
 */
class PromoCodeFlag
{
    public const TYPE_INCLUSIVE = 'with';
    public const TYPE_EXCLUSIVE = 'without';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode")
     * @ORM\JoinColumn(nullable=false, name="promo_code_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected $promoCode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Product\Flag\Flag")
     * @ORM\JoinColumn(nullable=false, name="flag_id", referencedColumnName="id", onDelete="CASCADE")
     * @ORM\Id
     */
    protected $flag;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\Flag $flag
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
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isInclusive(): bool
    {
        return $this->type === static::TYPE_INCLUSIVE;
    }

    /**
     * @return bool
     */
    public function isExclusive(): bool
    {
        return $this->type === static::TYPE_EXCLUSIVE;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function setPromoCode($promoCode): void
    {
        $this->promoCode = $promoCode;
    }
}
