<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Money;

use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use Brick\Math\RoundingMode;
use DomainException;
use InvalidArgumentException;
use JsonSerializable;
use Override;
use Shopsys\FrameworkBundle\Component\Money\Exception\InvalidNumericArgumentException;

class Money implements JsonSerializable
{
    protected function __construct(protected readonly BigDecimal $decimal)
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
        if (is_nan($float)) {
            throw new InvalidNumericArgumentException(
                'NAN',
                new InvalidArgumentException('NAN and INF values are not supported'),
            );
        }

        if (is_infinite($float)) {
            throw new InvalidNumericArgumentException(
                $float > 0 ? 'INF' : '-INF',
                new InvalidArgumentException('NAN and INF values are not supported'),
            );
        }

        $decimal = static::createDecimal((string)$float, $scale);

        return new static($decimal);
    }

    public static function zero(): static
    {
        return static::create(0);
    }

    public function getAmount(): string
    {
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
        $resultDecimal = $this->decimal->plus($money->decimal);

        return new static($resultDecimal);
    }

    public function subtract(self $money): static
    {
        $resultDecimal = $this->decimal->minus($money->decimal);

        return new static($resultDecimal);
    }

    public function multiply(int|string $multiplier): static
    {
        $decimalMultiplier = self::createDecimal($multiplier);
        $resultDecimal = $this->decimal->multipliedBy($decimalMultiplier);

        return new static($resultDecimal);
    }

    public function divide(int|string $divisor, int $scale): static
    {
        $decimalDivisor = self::createDecimal($divisor);

        if ($this->decimal->isZero()) {
            return $this->round($scale);
        }

        if ($decimalDivisor->isZero()) {
            throw new DomainException('Division by zero.');
        }

        $resultDecimal = $this->decimal->dividedBy($decimalDivisor, $scale, RoundingMode::HALF_UP);

        return new static($resultDecimal);
    }

    public function round(int $scale): static
    {
        $decimal = $this->decimal->toScale(min($scale, $this->decimal->getScale()), RoundingMode::HALF_UP);

        return new static($decimal);
    }

    public function equals(self $money): bool
    {
        return $this->decimal->isEqualTo($money->decimal);
    }

    /**
     * @return int same as spaceship operator (<=>)
     */
    public function compare(self $money): int
    {
        return $this->decimal->compareTo($money->decimal);
    }

    public function isGreaterThan(self $money): bool
    {
        return $this->decimal->isGreaterThan($money->decimal);
    }

    public function isGreaterThanOrEqualTo(self $money): bool
    {
        return $this->decimal->isGreaterThanOrEqualTo($money->decimal);
    }

    public function isLessThan(self $money): bool
    {
        return $this->decimal->isLessThan($money->decimal);
    }

    public function isLessThanOrEqualTo(self $money): bool
    {
        return $this->decimal->isLessThanOrEqualTo($money->decimal);
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

    protected static function createDecimal(int|string $value, ?int $scale = null): BigDecimal
    {
        if (is_string($value) && !preg_match('/^[+-]?(\d+(\.\d+)?)([eE][+-]?\d+)?$/', $value)) {
            throw new InvalidNumericArgumentException($value, new InvalidArgumentException(sprintf('Invalid numeric value: "%s"', $value)));
        }

        if ($scale !== null && $scale < 0) {
            throw new InvalidNumericArgumentException($value, new InvalidArgumentException(sprintf('Scale cannot be negative, got %d.', $scale)));
        }

        try {
            $decimal = BigDecimal::of($value);

            if ($scale !== null) {
                return $decimal->toScale($scale, RoundingMode::HALF_UP);
            }

            return $decimal;
        } catch (MathException $e) {
            throw new InvalidNumericArgumentException($value, $e);
        }
    }
}
