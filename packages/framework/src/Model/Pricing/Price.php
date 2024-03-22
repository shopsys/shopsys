<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\Money\Money;

/**
 * @ORM\Embeddable
 */
class Price
{
    protected Money $vatAmount;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithoutVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithVat
     */
    public function __construct(
        /** @ORM\Column(type="money", precision=20, scale=6, name="without_vat") */
        protected readonly Money $priceWithoutVat,
        /** @ORM\Column(type="money", precision=20, scale=6, name="with_vat") */
        protected readonly Money $priceWithVat,
    ) {
        $this->vatAmount = $priceWithVat->subtract($priceWithoutVat);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public static function zero(): self
    {
        return new self(Money::zero(), Money::zero());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPriceWithoutVat(): Money
    {
        return $this->priceWithoutVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPriceWithVat(): Money
    {
        return $this->priceWithVat;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getVatAmount(): Money
    {
        return $this->vatAmount;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $priceToAdd
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function add(self $priceToAdd): self
    {
        return new self(
            $this->priceWithoutVat->add($priceToAdd->priceWithoutVat),
            $this->priceWithVat->add($priceToAdd->priceWithVat),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $priceToSubtract
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function subtract(self $priceToSubtract): self
    {
        return new self(
            $this->priceWithoutVat->subtract($priceToSubtract->priceWithoutVat),
            $this->priceWithVat->subtract($priceToSubtract->priceWithVat),
        );
    }

    /**
     * @param int|string $multiplier
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function multiply(int|string $multiplier): self
    {
        return new self(
            $this->priceWithoutVat->multiply($multiplier),
            $this->priceWithVat->multiply($multiplier),
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function inverse(): self
    {
        return $this->multiply(-1);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @return bool
     */
    public function equals(self $price): bool
    {
        return $this->priceWithoutVat->equals($price->priceWithoutVat)
            && $this->priceWithVat->equals($price->priceWithVat);
    }

    /**
     * @return bool
     */
    public function isZero(): bool
    {
        return $this->priceWithoutVat->isZero() && $this->priceWithVat->isZero();
    }
}
