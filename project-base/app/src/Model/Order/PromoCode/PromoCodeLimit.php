<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PromoCodeLimit
{
    /**
     * @var \App\Model\Order\PromoCode\PromoCode
     * @ORM\ManyToOne(targetEntity="App\Model\Order\PromoCode\PromoCode", )
     * @ORM\JoinColumn(nullable=false, onDelete="cascade")
     * @ORM\Id
     */
    private $promoCode;

    /**
     * @var string
     * @ORM\Column(type="decimal", precision=20, scale=4)
     * @ORM\Id
     */
    private $fromPriceWithVat;

    /**
     * @var string
     * @ORM\Column(type="decimal", precision=20, scale=4)
     */
    private $discount;

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
    public function getFromPriceWithVat(): ?string
    {
        return $this->fromPriceWithVat;
    }

    /**
     * @return string
     */
    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function setPromoCode(PromoCode $promoCode): void
    {
        $this->promoCode = $promoCode;
    }
}
