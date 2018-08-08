<?php

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="promo_codes")
 * @ORM\Entity
 */
class PromoCode
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="text", unique=true)
     */
    protected $code;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=20, scale=4)
     */
    protected $percent;

    public function __construct(PromoCodeData $promoCodeData)
    {
        $this->code = $promoCodeData->code;
        $this->percent = $promoCodeData->percent;
    }

    public function edit(PromoCodeData $promoCodeData): void
    {
        $this->code = $promoCodeData->code;
        $this->percent = $promoCodeData->percent;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getPercent(): float
    {
        return $this->percent;
    }
}
