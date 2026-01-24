<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Money;

use Override;

class HiddenMoney extends Money
{
    public const string HIDDEN_FORMAT = '***';

    public function __construct()
    {
    }

    #[Override]
    public static function create($value): static
    {
        return new static();
    }

    #[Override]
    public static function createFromFloat(float $float, int $scale): static
    {
        return new static();
    }

    #[Override]
    public static function zero(): static
    {
        return new static();
    }

    #[Override]
    public function getAmount(): string
    {
        return self::HIDDEN_FORMAT;
    }

    #[Override]
    public function add(Money $money): static
    {
        return $this;
    }

    #[Override]
    public function subtract(Money $money): static
    {
        return $this;
    }

    #[Override]
    public function multiply($multiplier): static
    {
        return $this;
    }

    #[Override]
    public function divide($divisor, int $scale): static
    {
        return $this;
    }

    #[Override]
    public function round(int $scale): static
    {
        return $this;
    }

    #[Override]
    public function equals(Money $money): bool
    {
        return false;
    }

    #[Override]
    public function compare(Money $money): int
    {
        return -1;
    }

    #[Override]
    public function isGreaterThan(Money $money): bool
    {
        return false;
    }

    #[Override]
    public function isGreaterThanOrEqualTo(Money $money): bool
    {
        return false;
    }

    #[Override]
    public function isLessThan(Money $money): bool
    {
        return false;
    }

    #[Override]
    public function isLessThanOrEqualTo(Money $money): bool
    {
        return false;
    }

    #[Override]
    public function isNegative(): bool
    {
        return false;
    }

    #[Override]
    public function isPositive(): bool
    {
        return false;
    }

    #[Override]
    public function isZero(): bool
    {
        return false;
    }
}
