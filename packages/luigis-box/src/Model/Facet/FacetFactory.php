<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Facet;

use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\LuigisBoxBundle\Model\Product\Filter\LuigisBoxFacetsToProductFilterOptionsMapper;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class FacetFactory
{
    /**
     * @return string[]
     */
    public function mapFacetsFromProductFilterData(ProductFilterData $productFilterData): array
    {
        $facets = [];

        foreach ($productFilterData->parameters as $parameterFilterData) {
            $facets[] = $parameterFilterData->parameter->getName();
        }

        return $facets;
    }

    /**
     * @return string[]
     */
    public function getDefaultFacetNamesByType(string $type): array
    {
        return match ($type) {
            TypeInLuigisBoxEnum::PRODUCT => LuigisBoxFacetsToProductFilterOptionsMapper::PRODUCT_FACET_NAMES,
            default => [],
        };
    }
}
