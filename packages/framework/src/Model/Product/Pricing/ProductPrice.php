<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

final class ProductPrice implements PriceInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $price
     * @param bool $priceFrom
     */
    public function __construct(
        private readonly Price $price,
        private readonly bool $priceFrom = false,
    ) {
    }

    /**
     * @return bool
     */
    public function isPriceFrom(): bool
    {
        return $this->priceFrom;
    }

    /**
     * @return self
     */
    public static function createHiddenProductPrice(): self
    {
        return new self(
            Price::createHiddenPrice(),
            false,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getPrice(): Price
    {
        return $this->price;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    #[Override]
    public function getPriceWithoutVat(): Money
    {
        return $this->price->getPriceWithoutVat();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    #[Override]
    public function getPriceWithVat(): Money
    {
        return $this->price->getPriceWithVat();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    #[Override]
    public function getVatAmount(): Money
    {
        return $this->price->getVatAmount();
    }
}
