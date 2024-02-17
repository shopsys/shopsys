<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;

class ProductAvailabilityExportScope extends AbstractProductExportScope
{
    public function __construct(
        private readonly ProductAvailabilityFacade $productAvailabilityFacade,
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
            'availability' => $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($object, $domainId),
            'availability_dispatch_time' => $this->productAvailabilityFacade->getProductAvailabilityDaysByDomainId($object, $domainId),
            'in_stock' => $this->productAvailabilityFacade->isProductAvailableOnDomainCached($object, $domainId),
            'stock_quantity' => $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($object, $domainId),
        ];
    }
}
