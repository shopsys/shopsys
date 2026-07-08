<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing;

use Override;
use Shopsys\FrameworkBundle\Component\Money\HiddenMoney;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\CurrencyMismatchException;

final class Price implements PriceInterface
{
    private Money $vatAmount;

    public function __construct(
        private readonly Money $priceWithoutVat,
        private readonly Money $priceWithVat,
        private readonly ?Currency $currency = null,
    ) {
        $this->vatAmount = $priceWithVat->subtract($priceWithoutVat);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function zero(): static
    {
        return new self(Money::zero(), Money::zero());
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriceWithoutVat(): Money
    {
        return $this->priceWithoutVat;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriceWithVat(): Money
    {
        return $this->priceWithVat;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getVatAmount(): Money
    {
        return $this->vatAmount;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function add(PriceInterface $priceToAdd): static
    {
        return new self(
            $this->priceWithoutVat->add($priceToAdd->getPriceWithoutVat()),
            $this->priceWithVat->add($priceToAdd->getPriceWithVat()),
            $this->resolveResultCurrency($priceToAdd),
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function subtract(PriceInterface $priceToSubtract): static
    {
        return new self(
            $this->priceWithoutVat->subtract($priceToSubtract->getPriceWithoutVat()),
            $this->priceWithVat->subtract($priceToSubtract->getPriceWithVat()),
            $this->resolveResultCurrency($priceToSubtract),
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function multiply(int|string $multiplier): static
    {
        return new self(
            $this->priceWithoutVat->multiply($multiplier),
            $this->priceWithVat->multiply($multiplier),
            $this->currency,
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function inverse(): static
    {
        return $this->multiply(-1);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function equals(PriceInterface $price): bool
    {
        return $this->priceWithoutVat->equals($price->getPriceWithoutVat())
            && $this->priceWithVat->equals($price->getPriceWithVat());
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isZero(): bool
    {
        return $this->priceWithoutVat->isZero() && $this->priceWithVat->isZero();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function createHiddenPrice(): static
    {
        return new self(
            new HiddenMoney(),
            new HiddenMoney(),
        );
    }

    /**
     * A currency-less price adopts the currency of the other operand, mixing two different currencies is forbidden
     */
    private function resolveResultCurrency(PriceInterface $otherPrice): ?Currency
    {
        $otherCurrency = $otherPrice->getCurrency();

        if ($this->currency !== null
            && $otherCurrency !== null
            && $this->currency->getCode() !== $otherCurrency->getCode()
        ) {
            throw new CurrencyMismatchException($this->currency->getCode(), $otherCurrency->getCode());
        }

        return $this->currency ?? $otherCurrency;
    }
}
