<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;

interface PriceInterface
{
    /**
     * @return self
     */
    public static function zero(): self;

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPriceWithoutVat(): Money;

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getPriceWithVat(): Money;

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getVatAmount(): Money;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $priceToAdd
     * @return self
     */
    public function add(self $priceToAdd): self;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $priceToSubtract
     * @return self
     */
    public function subtract(self $priceToSubtract): self;

    /**
     * @param int|string $multiplier
     * @return self
     */
    public function multiply(int|string $multiplier): self;

    /**
     * @return self
     */
    public function inverse(): self;

    /**
     * @param self $price
     * @return bool
     */
    public function equals(self $price): bool;

    /**
     * @return bool
     */
    public function isZero(): bool;

    /**
     * @return self
     */
    public static function createHiddenPrice(): self;
}
