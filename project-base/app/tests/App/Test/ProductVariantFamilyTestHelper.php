<?php

declare(strict_types=1);

namespace Tests\App\Test;

use Shopsys\FrameworkBundle\Model\Product\Product;

final class ProductVariantFamilyTestHelper
{
    /**
     * @return int[]
     */
    public static function getVariantFamilyProductIds(Product $mainVariant): array
    {
        return [
            $mainVariant->getId(),
            ...array_map(static fn (Product $variant): int => $variant->getId(), $mainVariant->getVariants()),
        ];
    }
}
