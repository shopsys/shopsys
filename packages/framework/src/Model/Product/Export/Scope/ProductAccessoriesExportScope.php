<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductAccessoriesExportScope extends AbstractProductExportScope
{
    public function __construct(
        private readonly ProductAccessoryFacade $productAccessoryFacade
    )
    {
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
            'accessories' => $this->extractAccessoriesIds($object),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return array
     */
    protected function extractAccessoriesIds(Product $product): array
    {
        $accessoriesIds = [];
        $accessories = $this->productAccessoryFacade->getAllAccessories($product);

        foreach ($accessories as $accessory) {
            $accessoriesIds[] = $accessory->getAccessory()->getId();
        }

        return $accessoriesIds;
    }
}