<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Brand;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Override;
use Shopsys\FrontendApiBundle\Model\Resolver\Brand\Search\BrandSearchResultsProviderInterface;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory;
use Shopsys\LuigisBoxBundle\Model\Provider\SearchResultsProvider;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class BrandSearchResultsProvider extends SearchResultsProvider implements BrandSearchResultsProviderInterface
{
    protected const int SEARCH_LIMIT = 50;

    public function __construct(
        string $enabledDomainIds,
        protected readonly DataLoaderInterface $luigisBoxBatchLoader,
        protected readonly LuigisBoxBatchLoadDataFactory $luigisBoxBatchLoadDataFactory,
        protected readonly LuigisBoxClient $luigisBoxClient,
        protected readonly LuigisBoxBrandSearchResultsMapper $luigisBoxBrandSearchResultsMapper,
    ) {
        parent::__construct($enabledDomainIds);
    }

    #[Override]
    public function getBrandSearchResults(
        Argument $argument,
    ): Promise|array {
        if ($argument['searchInput']['isAutocomplete'] !== true) {
            $luigisBoxBatchLoadData = $this->luigisBoxBatchLoadDataFactory->createForSearch(
                TypeInLuigisBoxEnum::BRAND,
                static::SEARCH_LIMIT,
                0,
                $argument,
            );

            $luigisBoxResults = $this->luigisBoxClient->getData($luigisBoxBatchLoadData, [
                TypeInLuigisBoxEnum::BRAND => static::SEARCH_LIMIT,
            ]);

            return $this->luigisBoxBrandSearchResultsMapper->mapBrandData($luigisBoxResults[TypeInLuigisBoxEnum::BRAND]);
        }

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
