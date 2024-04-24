<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PromoCodeLimit
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode", )
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     * @ORM\Id
     */
    protected $promoCode;

    /**
     * @var string
     * @ORM\Column(type="decimal", precision=20, scale=4)
     * @ORM\Id
     */
    protected $fromPriceWithVat;

    /**
     * @var string
     * @ORM\Column(type="decimal", precision=20, scale=4)
     */
    protected $discount;

    /**
     * @param string $from
     * @param string $discount
     */
    public function __construct(string $from, string $discount)
    {
        $this->fromPriceWithVat = $from;
        $this->discount = $discount;
    }

    /**
     * @return string
     */
    public function getFromPriceWithVat()
    {
        return $this->fromPriceWithVat;
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
