<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum;
use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductVariantsExportScope extends AbstractProductExportScope
{
    public function getPreconditions(): array
    {
        return [
            ProductExportPreconditionsEnum::VISIBILITY_RECALCULATION,
            ProductExportPreconditionsEnum::SELLING_DENIED_RECALCULATION,
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $object
     * @param string $locale
     * @param int $domainId
     * @return array
     */
    public function map(object $object, string $locale, int $domainId): array
    {
        return [
            'variants' => $this->extractVariantIds($object),
            'is_variant' => $object->isVariant(),
            'is_main_variant' => $object->isMainVariant(),
            'main_variant_id' => $object->isVariant() ? $object->getMainVariant()?->getId() : null,
        ];
    }

    public function getDependencies(): array
    {
        return [
            ProductSellingDeniedExportScope::class,
            ProductVisibilityExportScope::class,
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return int[]
     */
    protected function extractVariantIds(Product $product): array
    {
        $variantIds = [];

        foreach ($product->getVariants() as $variant) {
            $variantIds[] = $variant->getId();
        }

        return $variantIds;
    }
}
