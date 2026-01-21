<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;

#[ORM\Entity]
class PromoCodeLimit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     */
    #[ORM\JoinColumn(nullable: false, onDelete: 'cascade')]
    #[ORM\ManyToOne(targetEntity: PromoCode::class)]
    #[ORM\Id]
    protected $promoCode;

    /**
     * @var string
     */
    #[ORM\Column(type: 'decimal', precision: 20, scale: 6)]
    #[ORM\Id]
    protected $fromPrice;

    /**
     * @var string
     */
    #[ORM\Column(type: 'decimal', precision: 20, scale: 6)]
    protected $discount;

    /**
     * @param string $from
     * @param string $discount
     */
    public function __construct(string $from, string $discount)
    {
        $this->fromPrice = $from;
        $this->discount = $discount;
    }

    /**
     * @return string
     */
    public function getFromPrice()
    {
        return $this->fromPrice;
    }

    /**
     * @return string
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function setPromoCode($promoCode): void
    {
        $this->promoCode = $promoCode;
    }
}
