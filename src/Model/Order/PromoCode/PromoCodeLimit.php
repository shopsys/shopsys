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
     *
     * @ORM\ManyToOne(targetEntity="App\Model\Order\PromoCode\PromoCode")
     * @ORM\JoinColumn(nullable=false)
     * @ORM\Id
     */
    private $promoCode;

    /**
     * @var int
     *
     * @ORM\Column(type="integer",name="`from`")
     * @ORM\Id
     */
    private $from;

    /**
     * @var string
     *
     * @ORM\Column(type="decimal", precision=20, scale=4)
     * @ORM\Id
     */
    private $percent;

    /**
     * @param int $from
     * @param string $percent
     */
    public function __construct(int $from, string $percent)
    {
        $this->from = $from;
        $this->percent = $percent;
    }

    /**
     * @return int
     */
    public function getFrom(): ?int
    {
        return $this->from;
    }

    /**
     * @return string
     */
    public function getPercent(): ?string
    {
        return $this->percent;
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     */
    public function setPromoCode(PromoCode $promoCode): void
    {
        $this->promoCode = $promoCode;
    }

    /**
     * @return \App\Model\Order\PromoCode\PromoCode
     */
    public function getPromoCode(): PromoCode
    {
        return $this->promoCode;
    }
}
