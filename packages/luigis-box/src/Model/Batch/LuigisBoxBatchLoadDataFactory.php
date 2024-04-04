<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Batch;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter\ProductFilterToLuigisBoxFilterMapper;
use Shopsys\LuigisBoxBundle\Model\Endpoint\LuigisBoxEndpointEnum;
use Shopsys\LuigisBoxBundle\Model\Product\Filter\LuigisBoxFacetsToProductFilterOptionsMapper;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class LuigisBoxBatchLoadDataFactory
{
    /**
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter\ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper
     * @param \Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum $typeInLuigisBoxEnum
     */
    public function __construct(
        protected readonly ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper,
        protected readonly TypeInLuigisBoxEnum $typeInLuigisBoxEnum,
    ) {
    }

    /**
     * @param string $type
     * @param int $limit
     * @param int $page
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param array $luigisBoxFilter
     * @return \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData
     */
    public function createForSearch(
        string $type,
        int $limit,
        int $page,
        Argument $argument,
        array $luigisBoxFilter = [],
    ): LuigisBoxBatchLoadData {
        $this->typeInLuigisBoxEnum->validateCase($type);

        if ($luigisBoxFilter === []) {
            $luigisBoxFilter = $this->productFilterToLuigisBoxFilterMapper->mapOnlyType($type);
        }

        $search = $argument['searchInput']['search'] ?? '';
        $orderingMode = $argument['orderingMode'];
        $endpoint = $argument['searchInput']['isAutocomplete'] === true ? LuigisBoxEndpointEnum::AUTOCOMPLETE : LuigisBoxEndpointEnum::SEARCH;
        $userIdentifier = $argument['searchInput']['userIdentifier'];

        return new LuigisBoxSearchBatchLoadData(
            $type,
            $endpoint,
            $userIdentifier,
            $limit,
            $search,
            $page,
            $luigisBoxFilter,
            $orderingMode,
            $this->getFacetNamesByType($type),
        );
    }

    /**
     * @param string $type
     * @return string[]
     */
    protected function getFacetNamesByType(string $type): array
    {
        return match ($type) {
            TypeInLuigisBoxEnum::PRODUCT => LuigisBoxFacetsToProductFilterOptionsMapper::PRODUCT_FACET_NAMES,
            default => [],
        };
    }
}
