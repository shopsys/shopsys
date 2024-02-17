<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Export\Scope;

use Shopsys\FrameworkBundle\Model\Product\Export\Preconditions\ProductExportPreconditionsEnum;
use Shopsys\FrameworkBundle\Model\Product\Export\ProductExportFieldEnum;

class ProductSellingDeniedExportScope extends AbstractProductExportScope
{
    public function getPreconditions(): array
    {
        return [
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
            'selling_denied' => $object->isSellingDenied(),
            'calculated_selling_denied' => $object->getCalculatedSellingDenied(),
        ];
    }
}
