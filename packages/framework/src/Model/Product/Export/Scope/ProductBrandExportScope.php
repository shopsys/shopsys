<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Brand\BrandCachedFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductBrandExportScope extends AbstractProductExportScope
{
    public function __construct(
        private readonly BrandCachedFacade $brandCachedFacade
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
            'brand' => $object->getBrand() ? $object->getBrand()->getId() : '',
            'brand_name' => $object->getBrand() ? $object->getBrand()->getName() : '',
            'brand_url' => $this->getBrandUrlForDomainByProduct($object, $domainId),
        ];
    }

    private function getBrandUrlForDomainByProduct(Product $product, int $domainId): string
    {
        $brand = $product->getBrand();

        if ($brand === null) {
            return '';
        }

        return $this->brandCachedFacade->getBrandUrlByDomainId($brand->getId(), $domainId);
    }
}
