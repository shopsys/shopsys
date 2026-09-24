<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Watcher;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class WatchedPriceComparator
{
    public function isPriceChanged(
        PriceInterface $currentPrice,
        ?Money $watchedPriceWithVat,
        ?Money $watchedPriceWithoutVat,
    ): bool {
        if ($watchedPriceWithVat === null || !$currentPrice->getPriceWithVat()->equals($watchedPriceWithVat)) {
            return true;
        }

        if ($watchedPriceWithoutVat === null) {
            return false;
        }

        return !$currentPrice->getPriceWithoutVat()->equals($watchedPriceWithoutVat);
    }
}
