<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Money;

use JsonSerializable;
use Litipk\BigNumbers\Decimal;
use Shopsys\FrameworkBundle\Component\Money\Exception\UnsupportedTypeException;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class BetterMoney implements JsonSerializable
{
    /**
     * @var \Litipk\BigNumbers\Decimal
     */
    protected $decimal;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
     */
    protected $currency;

    /**
     * @param \Litipk\BigNumbers\Decimal $decimal
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     */
    protected function __construct(Decimal $decimal, Currency $currency)
    {
        $this->decimal = $decimal;
        $this->currency = $currency;
    }

    /**
     * @param int|string $value
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     *
     * @return \Shopsys\FrameworkBundle\Component\Money\BetterMoney
     * @throws \Shopsys\FrameworkBundle\Component\Money\Exception\InvalidNumericArgumentException
     */
    public static function create($value, Currency $currency): self
    {
        return new self(self::createDecimal($value), $currency);
    }

    /**
     * @param float $float
     * @param int $scale must be specified when creating from floats to avoid issues with precision
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     *
     * @return \Shopsys\FrameworkBundle\Component\Money\BetterMoney
     * @throws \Shopsys\FrameworkBundle\Component\Money\Exception\InvalidNumericArgumentException
     */
    public static function createFromFloat(float $float, int $scale, Currency $currency): self
    {
        // Using Decimal::fromString as the Decimal::fromFloat has issues with specified scale
        // See https://github.com/Litipk/php-bignumbers/pull/67 for details
        $decimal = self::createDecimal((string)$float, $scale);

        return new self($decimal, $currency);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     *
     * @return \Shopsys\FrameworkBundle\Component\Money\BetterMoney
     * @throws \Shopsys\FrameworkBundle\Component\Money\Exception\InvalidNumericArgumentException
     */
    public static function zero(Currency $currency): self
    {
        return self::create(0, $currency);
    }

    /**
     * @return \Litipk\BigNumbers\Decimal
     */
    public function getAmount(): Decimal
    {
        if ($this->decimal->isZero() && $this->decimal->isNegative()) {
            return $this->decimal->mul(Decimal::fromString('-1'));
        }

        return $this->decimal;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @return string[]
     */
    public function jsonSerialize(): array
    {
        return [
            'amount' => (string)$this->getAmount(),
            'currencyCode' => $this->currency->getCode(),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\BetterMoney $money
     *
     * @return bool
     */
    protected function isCurrencySame(self $money): bool
    {
        return $this->currency->getCode() === $money->getCurrency()->getCode();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\BetterMoney $money
     *
     * @return \Shopsys\FrameworkBundle\Component\Money\BetterMoney
     */
    public function add(self $money): self
    {
        if (!$this->isCurrencySame($money)) {
            throw new CurrencyMismatchException($this->getCurrency(), $money->getCurrency());
        }

        $resultDecimal = $this->decimal->add($money->decimal);

        return new self($resultDecimal, $this->getCurrency());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\BetterMoney $money
     *
     * @return \Shopsys\FrameworkBundle\Component\Money\BetterMoney
     */
    public function subtract(self $money): self
    {
        if (!$this->isCurrencySame($money)) {
            throw new CurrencyMismatchException($this->getCurrency(), $money->getCurrency());
        }

        $resultDecimal = $this->decimal->sub($money->decimal);

        return new self($resultDecimal, $this->getCurrency());
    }

    /**
     * @param int|string $multiplier
     *
     * @return \Shopsys\FrameworkBundle\Component\Money\BetterMoney
     */
    public function multiply($multiplier): self
    {
        $decimalMultiplier = self::createDecimal($multiplier);
        $resultDecimal = $this->decimal->mul($decimalMultiplier);

        return new self($resultDecimal, $this->getCurrency());
    }

    /**
     * @param int|string $divisor
     * @param int $scale
     *
     * @return \Shopsys\FrameworkBundle\Component\Money\BetterMoney
     */
    public function divide($divisor, int $scale): self
    {
        $decimalDivisor = self::createDecimal($divisor);

        // Decimal internally ignores scale when number is zero
        if ($this->decimal->isZero()) {
            return $this->round($scale);
        }

        $resultDecimal = $this->decimal->div($decimalDivisor, $scale);

        return new self($resultDecimal, $this->getCurrency());
    }

    /**
     * @param int $scale
     *
     * @return \Shopsys\FrameworkBundle\Component\Money\BetterMoney
     */
    public function round(int $scale): self
    {
        $decimal = $this->decimal->round($scale);

        return new self($decimal, $this->getCurrency());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\BetterMoney $money
     *
     * @return bool
     */
    public function equals(self $money): bool
    {
        if (!$this->isCurrencySame($money)) {
            throw new CurrencyMismatchException($this->getCurrency(), $money->getCurrency());
        }

        return $this->decimal->equals($money->decimal);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\BetterMoney $money
     *
     * @return int same as spaceship operator (<=>)
     */
    public function compare(self $money): int
    {
        if (!$this->isCurrencySame($money)) {
            throw new CurrencyMismatchException($this->getCurrency(), $money->getCurrency());
        }

        return $this->decimal->comp($money->decimal);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\BetterMoney $money
     *
     * @return bool
     */
    public function isGreaterThan(self $money): bool
    {
        if (!$this->isCurrencySame($money)) {
            throw new CurrencyMismatchException($this->getCurrency(), $money->getCurrency());
        }

        return $this->decimal->isGreaterThan($money->decimal);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\BetterMoney $money
     *
     * @return bool
     */
    public function isGreaterThanOrEqualTo(self $money): bool
    {
        if (!$this->isCurrencySame($money)) {
            throw new CurrencyMismatchException($this->getCurrency(), $money->getCurrency());
        }

        return $this->decimal->isGreaterOrEqualTo($money->decimal);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\BetterMoney $money
     *
     * @return bool
     */
    public function isLessThan(self $money): bool
    {
        if (!$this->isCurrencySame($money)) {
            throw new CurrencyMismatchException($this->getCurrency(), $money->getCurrency());
        }

        return $this->decimal->isLessThan($money->decimal);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\BetterMoney $money
     * @return bool
     */
    public function isLessThanOrEqualTo(self $money): bool
    {
        if (!$this->isCurrencySame($money)) {
            throw new CurrencyMismatchException($this->getCurrency(), $money->getCurrency());
        }

        return $this->decimal->isLessOrEqualTo($money->decimal);
    }

    /**
     * @return bool
     */
    public function isNegative(): bool
    {
        return $this->decimal->isNegative() && !$this->decimal->isZero();
    }

    /**
     * @return bool
     */
    public function isPositive(): bool
    {
        return $this->decimal->isPositive();
    }

    /**
     * @return bool
     */
    public function isZero(): bool
    {
        return $this->decimal->isZero();
    }

    /**
     * @param int|string $value
     * @param int|null $scale
     * @return \Litipk\BigNumbers\Decimal
     */
    protected static function createDecimal($value, ?int $scale = null): Decimal
    {
        if (is_int($value)) {
            return Decimal::fromInteger($value);
        }

        if (is_string($value)) {
            try {
                return Decimal::fromString($value, $scale);
            } catch (\Litipk\BigNumbers\Errors\BigNumbersError | \InvalidArgumentException $e) {
                throw new \Shopsys\FrameworkBundle\Component\Money\Exception\InvalidNumericArgumentException($value, $e);
            }
        }

        throw new UnsupportedTypeException($value, ['string', 'int']);
    }
}
