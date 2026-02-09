<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Symfony\Component\Clock\DatePoint;

class SpecialPrice
{
    public DateTimeInterface $validFrom;

    public DateTimeInterface $validTo;

    public PriceInterface $price;

    public int $productId;

    public int $priceListId;

    public string $priceListName;

    public function isFuturePrice(): bool
    {
        $now = new DatePoint();

        return $this->validFrom > $now;
    }

    public function isNowActive(): bool
    {
        $now = new DatePoint();

        return $this->validFrom <= $now && $this->validTo >= $now;
    }
}
