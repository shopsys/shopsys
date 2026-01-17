<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Price;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class PriceInfo
{
    public Money $priceWithoutVat;

    public Money $priceWithVat;

    public Money $vatAmount;

    public ?DateTimeInterface $nextPriceChange = null;

    public ?float $percentageDiscount = null;

    public PriceInterface $basicPrice;

    public bool $isPriceFrom;

    public function setSellingPrice(PriceInterface $price): void
    {
        $this->priceWithoutVat = $price->getPriceWithoutVat();
        $this->priceWithVat = $price->getPriceWithVat();
        $this->vatAmount = $price->getVatAmount();
    }
}
