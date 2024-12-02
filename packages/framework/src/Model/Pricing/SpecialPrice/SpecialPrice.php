<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\SpecialPrice;

use DateTimeImmutable;
use DateTimeInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class SpecialPrice
{
    public DateTimeInterface $validFrom;

    public DateTimeInterface $validTo;

    public Price $price;

    public int $productId;

    public int $priceListId;

    public string $priceListName;

    /**
     * @return bool
     */
    public function isFuturePrice(): bool
    {
        return $this->validFrom > new DateTimeImmutable();
    }

    /**
     * @return bool
     */
    public function isNowActive(): bool
    {
        return $this->validFrom <= new DateTimeImmutable() && $this->validTo >= new DateTimeImmutable();
    }
}
