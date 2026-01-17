<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;

interface PriceInterface
{
    public static function zero(): static;

    public function getPriceWithoutVat(): Money;

    public function getPriceWithVat(): Money;

    public function getVatAmount(): Money;

    public function add(self $priceToAdd): static;

    public function subtract(self $priceToSubtract): static;

    public function multiply(int|string $multiplier): static;

    public function inverse(): static;

    public function equals(self $price): bool;

    public function isZero(): bool;

    public static function createHiddenPrice(): static;
}
