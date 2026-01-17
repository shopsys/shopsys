<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;

interface PriceInterface
{
    public static function zero(): self;

    public function getPriceWithoutVat(): Money;

    public function getPriceWithVat(): Money;

    public function getVatAmount(): Money;

    public function add(self $priceToAdd): self;

    public function subtract(self $priceToSubtract): self;

    public function multiply(int|string $multiplier): self;

    public function inverse(): self;

    public function equals(self $price): bool;

    public function isZero(): bool;

    public static function createHiddenPrice(): self;
}
