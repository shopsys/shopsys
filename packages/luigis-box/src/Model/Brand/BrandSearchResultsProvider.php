<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Brand;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Resolver\Brand\Search\BrandSearchResultsProviderInterface;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory;
use Shopsys\LuigisBoxBundle\Model\Provider\SearchResultsProvider;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class BrandSearchResultsProvider extends SearchResultsProvider implements BrandSearchResultsProviderInterface
{
    protected const int SEARCH_LIMIT = 50;

    /**
     * @param string $enabledDomainIds
     * @param \Overblog\DataLoader\DataLoaderInterface $luigisBoxBatchLoader
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory $luigisBoxBatchLoadDataFactory
     */
    public function __construct(
        string $enabledDomainIds,
        protected readonly DataLoaderInterface $luigisBoxBatchLoader,
        protected readonly LuigisBoxBatchLoadDataFactory $luigisBoxBatchLoadDataFactory,
    ) {
        parent::__construct($enabledDomainIds);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \GraphQL\Executor\Promise\Promise|array
     */
    public function getBrandSearchResults(
        Argument $argument,
    ): Promise|array {
        return $this->luigisBoxBatchLoader->load(
            $this->luigisBoxBatchLoadDataFactory->createForSearch(
                TypeInLuigisBoxEnum::BRAND,
                static::SEARCH_LIMIT,
                0,
                $argument,
            ),
        );
    }
}
