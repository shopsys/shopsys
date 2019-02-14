<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Money;

use Litipk\BigNumbers\Decimal;

/**
 * Immutable object for representing a monetary value
 * Uses decimal numbers with specifiable number of decimal places
 */
class Money
{
    /**
     * @var \Litipk\BigNumbers\Decimal
     */
    protected $decimal;

    /**
     * @param \Litipk\BigNumbers\Decimal $decimal
     */
    protected function __construct(Decimal $decimal)
    {
        $this->decimal = $decimal;
    }

    /**
     * @param string $string
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public static function fromString(string $string): self
    {
        $decimal = Decimal::fromString($string);

        return new static($decimal);
    }

    /**
     * @param float $float
     * @param int $scale must be specified when creating from floats to avoid issues with precision
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public static function fromFloat(float $float, int $scale): self
    {
        // Using Decimal::fromString as the Decimal::fromFloat has issues with specified scale
        // See https://github.com/Litipk/php-bignumbers/pull/67 for details
        $decimal = Decimal::fromString((string)$float, $scale);

        return new static($decimal);
    }

    /**
     * @param int $integer
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public static function fromInteger(int $integer): self
    {
        $decimal = Decimal::fromInteger($integer);

        return new static($decimal);
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string)$this->decimal;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $money
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function add(self $money): self
    {
        $resultDecimal = $this->decimal->add($money->decimal);

        return new static($resultDecimal);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $money
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function subtract(self $money): self
    {
        $resultDecimal = $this->decimal->sub($money->decimal);

        return new static($resultDecimal);
    }

    /**
     * @param string $multiplier
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function multiply(string $multiplier): self
    {
        $decimalMultiplier = Decimal::fromString($multiplier);
        $resultDecimal = $this->decimal->mul($decimalMultiplier);

        return new static($resultDecimal);
    }

    /**
     * @param string $divisor
     * @param int $scale specifies scale for the division
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function divide(string $divisor, int $scale): self
    {
        $decimalDivisor = Decimal::fromString($divisor);
        $resultDecimal = $this->decimal->div($decimalDivisor, $scale);

        return new static($resultDecimal);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $divisor
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function mod(self $divisor): self
    {
        $resultDecimal = $this->decimal->mod($divisor->decimal);

        return new static($resultDecimal);
    }

    /**
     * @param int $scale
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function round(int $scale): self
    {
        $decimal = $this->decimal->round($scale);

        return new static($decimal);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $money
     * @return bool
     */
    public function equals(self $money): bool
    {
        return $this->decimal->equals($money->decimal);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $money
     * @return int
     */
    public function compare(self $money): int
    {
        return $this->decimal->comp($money->decimal);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $money
     * @return bool
     */
    public function isGreaterThan(self $money): bool
    {
        return $this->decimal->isGreaterThan($money->decimal);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $money
     * @return bool
     */
    public function isGreaterThanOrEqualTo(self $money): bool
    {
        return $this->decimal->isGreaterOrEqualTo($money->decimal);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $money
     * @return bool
     */
    public function isLessThan(self $money): bool
    {
        return $this->decimal->isLessThan($money->decimal);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $money
     * @return bool
     */
    public function isLessThanOrEqualTo(self $money): bool
    {
        return $this->decimal->isLessOrEqualTo($money->decimal);
    }

    /**
     * @param mixed $value
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     * @internal method for restricting the usage during refactoring
     */
    public static function fromValue($value): self
    {
        return new static(Decimal::create($value));
    }

    /**
     * @return string
     * @internal method for restricting the usage during refactoring
     */
    public function toValue(): string
    {
        return $this->decimal->innerValue();
    }
}
