<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Article;

use GraphQL\Executor\Promise\Promise;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Resolver\Article\Search\ArticlesSearchQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Article\Search\ArticlesSearchResultsProviderInterface;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadData;
use Shopsys\LuigisBoxBundle\Model\Provider\SearchResultsProvider;

class ArticlesSearchResultsProvider extends SearchResultsProvider implements ArticlesSearchResultsProviderInterface
{
    /**
     * @param string $enabledDomainIds
     * @param \Overblog\DataLoader\DataLoaderInterface $luigisBoxBatchLoader
     */
    public function __construct(
        string $enabledDomainIds,
        protected readonly DataLoaderInterface $luigisBoxBatchLoader,
    ) {
        parent::__construct($enabledDomainIds);
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \GraphQL\Executor\Promise\Promise|array
     */
    public function getArticlesSearchResults(
        Argument $argument,
    ): Promise|array {
        return $this->luigisBoxBatchLoader->load(
            new LuigisBoxBatchLoadData(
                'article',
                ArticlesSearchQuery::ARTICLE_SEARCH_LIMIT,
            ),
        );
    }
}
