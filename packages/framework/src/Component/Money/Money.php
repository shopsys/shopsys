<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Money;

use InvalidArgumentException;
use JsonSerializable;
use Litipk\BigNumbers\Decimal;
use Litipk\BigNumbers\Errors\BigNumbersError;
use Override;
use Shopsys\FrameworkBundle\Component\Money\Exception\InvalidNumericArgumentException;
use Shopsys\FrameworkBundle\Component\Money\Exception\UnsupportedTypeException;
use function substr;

class Money implements JsonSerializable
{
    protected function __construct(protected readonly Decimal $decimal)
    {
    }

    public static function create(int|string $value): static
    {
        $decimal = static::createDecimal($value);

        return new static($decimal);
    }

    /**
     * @param int $scale must be specified when creating from floats to avoid issues with precision
     */
    public static function createFromFloat(float $float, int $scale): static
    {
        // Using Decimal::fromString as the Decimal::fromFloat has issues with specified scale
        // See https://github.com/Litipk/php-bignumbers/pull/67 for details
        $decimal = static::createDecimal((string)$float, $scale);

        return new static($decimal);
    }

    public static function zero(): static
    {
        return static::create(0);
    }

    public function getAmount(): string
    {
        if ($this->decimal->isZero() && $this->decimal->isNegative()) {
            return substr((string)$this->decimal, 1);
        }

        return (string)$this->decimal;
    }

    /**
     * @return string[]
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'amount' => $this->getAmount(),
        ];
    }

    public function add(self $money): static
    {
        $resultDecimal = $this->decimal->add($money->decimal);

        return new static($resultDecimal);
    }

    public function subtract(self $money): static
    {
        $resultDecimal = $this->decimal->sub($money->decimal);

        return new static($resultDecimal);
    }

    public function multiply(int|string $multiplier): static
    {
        $decimalMultiplier = self::createDecimal($multiplier);
        $resultDecimal = $this->decimal->mul($decimalMultiplier);

        return new static($resultDecimal);
    }

    public function divide(int|string $divisor, int $scale): static
    {
        $decimalDivisor = self::createDecimal($divisor);

        // Decimal internally ignores scale when number is zero
        if ($this->decimal->isZero()) {
            return $this->round($scale);
        }

        $resultDecimal = $this->decimal->div($decimalDivisor, $scale);

        return new static($resultDecimal);
    }

    public function round(int $scale): static
    {
        $decimal = $this->decimal->round($scale);

        return new static($decimal);
    }

    public function equals(self $money): bool
    {
        return $this->decimal->equals($money->decimal);
    }

    /**
     * @return int same as spaceship operator (<=>)
     */
    public function compare(self $money): int
    {
        return $this->decimal->comp($money->decimal);
    }

    public function isGreaterThan(self $money): bool
    {
        return $this->decimal->isGreaterThan($money->decimal);
    }

    public function isGreaterThanOrEqualTo(self $money): bool
    {
        return $this->decimal->isGreaterOrEqualTo($money->decimal);
    }

    public function isLessThan(self $money): bool
    {
        return $this->decimal->isLessThan($money->decimal);
    }

    public function isLessThanOrEqualTo(self $money): bool
    {
        return $this->decimal->isLessOrEqualTo($money->decimal);
    }

    public function isNegative(): bool
    {
        return $this->decimal->isNegative() && !$this->decimal->isZero();
    }

    public function isPositive(): bool
    {
        return $this->decimal->isPositive();
    }

    public function isZero(): bool
    {
        return $this->decimal->isZero();
    }

    protected static function createDecimal(int|string $value, ?int $scale = null): Decimal
    {
        if (is_int($value)) {
            return Decimal::fromInteger($value);
        }

        if (is_string($value)) {
            try {
                return Decimal::fromString($value, $scale);
            } catch (BigNumbersError | InvalidArgumentException $e) {
                throw new InvalidNumericArgumentException($value, $e);
            }
        }

        throw new UnsupportedTypeException($value, ['string', 'int']);
    }
}
