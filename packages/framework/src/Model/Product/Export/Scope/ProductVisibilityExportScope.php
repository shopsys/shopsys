<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum;
use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class ProductVisibilityExportScope extends AbstractProductExportScope
{
    public function __construct(
        private readonly ProductVisibilityFacade $productVisibilityFacade,
    )
    {}

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum[]
     */
    public function getPreconditions(): array
    {
        return [
            ProductExportPreconditionsEnum::VISIBILITY_RECALCULATION,
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
            'visibility' => $this->extractVisibility($domainId, $object),
        ];
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array
     */
    protected function extractVisibility(int $domainId, Product $product): array
    {
        $visibility = [];

        foreach ($this->productVisibilityFacade->findProductVisibilitiesByDomainIdAndProduct(
            $domainId,
            $product,
        ) as $productVisibility) {
            $visibility[] = [
                'pricing_group_id' => $productVisibility->getPricingGroup()->getId(),
                'visible' => $productVisibility->isVisible(),
            ];
        }

        return $visibility;
    }
}
