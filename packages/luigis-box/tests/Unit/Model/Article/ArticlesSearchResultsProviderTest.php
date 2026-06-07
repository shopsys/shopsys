<?php

declare(strict_types=1);

namespace Tests\LuigisBoxBundle\Unit\Model\Article;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use PHPUnit\Framework\TestCase;
use Shopsys\FrontendApiBundle\Model\Resolver\Article\Search\ArticlesSearchQuery;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult;
use Shopsys\LuigisBoxBundle\Model\Article\ArticlesSearchResultsProvider;
use Shopsys\LuigisBoxBundle\Model\Article\LuigisBoxArticleSearchResultsMapper;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxSearchBatchLoadData;
use Shopsys\LuigisBoxBundle\Model\Endpoint\LuigisBoxEndpointEnum;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class ArticlesSearchResultsProviderTest extends TestCase
{
    public function testRegularSearchLoadsArticlesAsMainLuigisBoxType(): void
    {
        $argument = $this->createSearchArgument(false);
        $luigisBoxBatchLoadData = $this->createLuigisBoxSearchBatchLoadData(LuigisBoxEndpointEnum::SEARCH);
        $luigisBoxResult = new LuigisBoxResult([1, 2, 3], ['article-1', 'blog_article-2', 'article-3'], 3, []);
        $expectedArticles = [
            ['name' => 'First article'],
            ['name' => 'Second article'],
            ['name' => 'Third article'],
        ];

        $luigisBoxBatchLoadDataFactory = $this->createMock(LuigisBoxBatchLoadDataFactory::class);
        $luigisBoxBatchLoadDataFactory->expects($this->once())
            ->method('createForSearch')
            ->with(TypeInLuigisBoxEnum::ARTICLE, ArticlesSearchQuery::ARTICLE_SEARCH_LIMIT, 0, $argument)
            ->willReturn($luigisBoxBatchLoadData);

        $luigisBoxClient = $this->createMock(LuigisBoxClient::class);
        $luigisBoxClient->expects($this->once())
            ->method('getData')
            ->with($luigisBoxBatchLoadData, [TypeInLuigisBoxEnum::ARTICLE => ArticlesSearchQuery::ARTICLE_SEARCH_LIMIT])
            ->willReturn([TypeInLuigisBoxEnum::ARTICLE => $luigisBoxResult]);

        $luigisBoxArticleSearchResultsMapper = $this->createMock(LuigisBoxArticleSearchResultsMapper::class);
        $luigisBoxArticleSearchResultsMapper->expects($this->once())
            ->method('mapArticleData')
            ->with($luigisBoxResult)
            ->willReturn($expectedArticles);

        $luigisBoxBatchLoader = $this->createMock(DataLoaderInterface::class);
        $luigisBoxBatchLoader->expects($this->never())->method('load');

        $articlesSearchResultsProvider = new ArticlesSearchResultsProvider(
            '',
            $luigisBoxBatchLoader,
            $luigisBoxBatchLoadDataFactory,
            $luigisBoxClient,
            $luigisBoxArticleSearchResultsMapper,
        );

        $actualArticles = $articlesSearchResultsProvider->getArticlesSearchResults($argument);

        self::assertSame($expectedArticles, $actualArticles);
    }

    public function testAutocompleteSearchUsesBatchedLuigisBoxRequest(): void
    {
        $argument = $this->createSearchArgument(true);
        $luigisBoxBatchLoadData = $this->createLuigisBoxSearchBatchLoadData(LuigisBoxEndpointEnum::AUTOCOMPLETE);
        $expectedArticles = [
            ['name' => 'Autocomplete article'],
        ];

        $luigisBoxBatchLoadDataFactory = $this->createMock(LuigisBoxBatchLoadDataFactory::class);
        $luigisBoxBatchLoadDataFactory->expects($this->once())
            ->method('createForSearch')
            ->with(TypeInLuigisBoxEnum::ARTICLE, ArticlesSearchQuery::ARTICLE_SEARCH_LIMIT, 0, $argument)
            ->willReturn($luigisBoxBatchLoadData);

        $luigisBoxBatchLoader = $this->createMock(DataLoaderInterface::class);
        $luigisBoxBatchLoader->expects($this->once())
            ->method('load')
            ->with($luigisBoxBatchLoadData)
            ->willReturn($expectedArticles);

        $luigisBoxClient = $this->createMock(LuigisBoxClient::class);
        $luigisBoxClient->expects($this->never())->method('getData');

        $luigisBoxArticleSearchResultsMapper = $this->createMock(LuigisBoxArticleSearchResultsMapper::class);
        $luigisBoxArticleSearchResultsMapper->expects($this->never())->method('mapArticleData');

        $articlesSearchResultsProvider = new ArticlesSearchResultsProvider(
            '',
            $luigisBoxBatchLoader,
            $luigisBoxBatchLoadDataFactory,
            $luigisBoxClient,
            $luigisBoxArticleSearchResultsMapper,
        );

        $actualArticles = $articlesSearchResultsProvider->getArticlesSearchResults($argument);

        self::assertSame($expectedArticles, $actualArticles);
    }

    private function createSearchArgument(bool $isAutocomplete): Argument
    {
        return new Argument([
            'searchInput' => [
                'search' => 'shipping',
                'isAutocomplete' => $isAutocomplete,
                'userIdentifier' => 'user-identifier',
            ],
            'orderingMode' => null,
        ]);
    }

    private function createLuigisBoxSearchBatchLoadData(string $endpoint): LuigisBoxSearchBatchLoadData
    {
        return new LuigisBoxSearchBatchLoadData(
            TypeInLuigisBoxEnum::ARTICLE,
            $endpoint,
            'user-identifier',
            ArticlesSearchQuery::ARTICLE_SEARCH_LIMIT,
            'shipping',
            0,
        );
    }
}
