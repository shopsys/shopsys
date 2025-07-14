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

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function create($value): Money
    {
        return new self();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function createFromFloat(float $float, int $scale): Money
    {
        return new self();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function zero(): Money
    {
        return new self();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAmount(): string
    {
        return self::HIDDEN_FORMAT;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function add(Money $money): Money
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function subtract(Money $money): Money
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function multiply($multiplier): Money
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function divide($divisor, int $scale): Money
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function round(int $scale): Money
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function equals(Money $money): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function compare(Money $money): int
    {
        return -1;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isGreaterThan(Money $money): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isGreaterThanOrEqualTo(Money $money): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isLessThan(Money $money): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isLessThanOrEqualTo(Money $money): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isNegative(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isPositive(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isZero(): bool
    {
        return false;
    }
}
