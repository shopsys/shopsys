<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'promo_code_flags')]
#[ORM\Entity]
class PromoCodeFlag
{
    public const TYPE_INCLUSIVE = 'with';
    public const TYPE_EXCLUSIVE = 'without';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'promo_code_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: PromoCode::class)]
    #[ORM\Id]
    protected $promoCode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\Flag
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false, name: 'flag_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Flag::class)]
    #[ORM\Id]
    protected $flag;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string')]
    protected $type;

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

    public function isInclusive(): bool
    {
        return $this->type === static::TYPE_INCLUSIVE;
    }

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
