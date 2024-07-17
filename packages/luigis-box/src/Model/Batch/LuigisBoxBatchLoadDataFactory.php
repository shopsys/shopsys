<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Batch;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter\ProductFilterToLuigisBoxFilterMapper;
use Shopsys\LuigisBoxBundle\Model\Endpoint\LuigisBoxEndpointEnum;
use Shopsys\LuigisBoxBundle\Model\Facet\FacetFactory;
use Shopsys\LuigisBoxBundle\Model\Type\RecommendationTypeEnum;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class LuigisBoxBatchLoadDataFactory
{
    /**
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter\ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper
     * @param \Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum $typeInLuigisBoxEnum
     * @param \Shopsys\LuigisBoxBundle\Model\Type\RecommendationTypeEnum $recommendationTypeEnum
     * @param \Shopsys\LuigisBoxBundle\Model\Facet\FacetFactory $facetFactory
     */
    public function __construct(
        protected readonly ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper,
        protected readonly TypeInLuigisBoxEnum $typeInLuigisBoxEnum,
        protected readonly RecommendationTypeEnum $recommendationTypeEnum,
        protected readonly FacetFactory $facetFactory,
    ) {
    }

    /**
     * @param string $type
     * @param int $limit
     * @param int $page
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param array $luigisBoxFilter
     * @param string[] $facetNames
     * @return \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData
     */
    public function createForSearch(
        string $type,
        int $limit,
        int $page,
        Argument $argument,
        array $luigisBoxFilter = [],
        array $facetNames = [],
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
            array_unique([...$this->facetFactory->getDefaultFacetNamesByType($type), ...$facetNames], SORT_REGULAR),
        );
    }

    /**
     * @param string $type
     * @param int $limit
     * @param array $itemIds
     * @param string $userIdentifier
     * @return \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData
     */
    public function createForRecommendation(
        string $type,
        int $limit,
        array $itemIds,
        string $userIdentifier,
    ): LuigisBoxBatchLoadData {
        $this->recommendationTypeEnum->validateCase($type);

        $endpoint = LuigisBoxEndpointEnum::RECOMMENDATIONS;

        return new LuigisBoxRecommendationBatchLoadData(
            $type,
            $endpoint,
            $userIdentifier,
            $limit,
            $itemIds,
        );
    }
}
