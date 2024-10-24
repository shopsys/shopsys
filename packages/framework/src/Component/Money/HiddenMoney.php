<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Money;

class HiddenMoney extends Money
{
    public const string HIDDEN_FORMAT = '***';

    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function create($value): Money
    {
        return new self();
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromFloat(float $float, int $scale): Money
    {
        return new self();
    }

    /**
     * {@inheritdoc}
     */
    public static function zero(): Money
    {
        return new self();
    }

    /**
     * {@inheritdoc}
     */
    public function getAmount(): string
    {
        return self::HIDDEN_FORMAT;
    }

    /**
     * {@inheritdoc}
     */
    public function add(Money $money): Money
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function subtract(Money $money): Money
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function multiply($multiplier): Money
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function divide($divisor, int $scale): Money
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function round(int $scale): Money
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(Money $money): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function compare(Money $money): int
    {
        return -1;
    }

    /**
     * {@inheritdoc}
     */
    public function isGreaterThan(Money $money): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isGreaterThanOrEqualTo(Money $money): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isLessThan(Money $money): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isLessThanOrEqualTo(Money $money): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isNegative(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isPositive(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isZero(): bool
    {
        return false;
    }
}
