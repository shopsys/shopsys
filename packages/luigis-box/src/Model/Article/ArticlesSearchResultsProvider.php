<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Article;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Override;
use Shopsys\FrontendApiBundle\Model\Resolver\Article\Search\ArticlesSearchQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Article\Search\ArticlesSearchResultsProviderInterface;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory;
use Shopsys\LuigisBoxBundle\Model\Provider\SearchResultsProvider;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class ArticlesSearchResultsProvider extends SearchResultsProvider implements ArticlesSearchResultsProviderInterface
{
    public function __construct(
        string $enabledDomainIds,
        protected readonly DataLoaderInterface $luigisBoxBatchLoader,
        protected readonly LuigisBoxBatchLoadDataFactory $luigisBoxBatchLoadDataFactory,
        protected readonly LuigisBoxClient $luigisBoxClient,
        protected readonly LuigisBoxArticleSearchResultsMapper $luigisBoxArticleSearchResultsMapper,
    ) {
        parent::__construct($enabledDomainIds);
    }

    #[Override]
    public function getArticlesSearchResults(
        Argument $argument,
    ): Promise|array {
        if ($argument['searchInput']['isAutocomplete'] !== true) {
            $luigisBoxBatchLoadData = $this->luigisBoxBatchLoadDataFactory->createForSearch(
                TypeInLuigisBoxEnum::ARTICLE,
                ArticlesSearchQuery::ARTICLE_SEARCH_LIMIT,
                0,
                $argument,
            );

            $luigisBoxResults = $this->luigisBoxClient->getData($luigisBoxBatchLoadData, [
                TypeInLuigisBoxEnum::ARTICLE => ArticlesSearchQuery::ARTICLE_SEARCH_LIMIT,
            ]);

            return $this->luigisBoxArticleSearchResultsMapper->mapArticleData($luigisBoxResults[TypeInLuigisBoxEnum::ARTICLE]);
        }

        return $this->luigisBoxBatchLoader->load(
            $this->luigisBoxBatchLoadDataFactory->createForSearch(
                TypeInLuigisBoxEnum::ARTICLE,
                ArticlesSearchQuery::ARTICLE_SEARCH_LIMIT,
                0,
                $argument,
            ),
        );
    }
}
