<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Batch;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter\ProductFilterToLuigisBoxFilterMapper;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;

class LuigisBoxBatchLoadDataFactory
{
    /**
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\Filter\ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper
     */
    public function __construct(
        protected readonly ProductFilterToLuigisBoxFilterMapper $productFilterToLuigisBoxFilterMapper,
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
    public function create(
        string $type,
        int $limit,
        int $page,
        Argument $argument,
        array $luigisBoxFilter = [],
    ): LuigisBoxBatchLoadData {
        if ($luigisBoxFilter === []) {
            $luigisBoxFilter = $this->productFilterToLuigisBoxFilterMapper->mapOnlyType($type);
        }

        $search = $argument['searchInput']['search'] ?? '';
        $orderingMode = $argument['orderingMode'];
        $endpoint = $argument['searchInput']['isAutocomplete'] === true ? LuigisBoxClient::ACTION_AUTOCOMPLETE : LuigisBoxClient::ACTION_SEARCH;
        $userIdentifier = $argument['searchInput']['userIdentifier'];

        return new LuigisBoxBatchLoadData(
            $type,
            $limit,
            $search,
            $endpoint,
            $page,
            $luigisBoxFilter,
            $userIdentifier,
            $orderingMode,
        );
    }
}
