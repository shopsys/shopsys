<?php

declare(strict_types=1);

namespace Tests\LuigisBoxBundle\Unit\Model\Category;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use PHPUnit\Framework\TestCase;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxSearchBatchLoadData;
use Shopsys\LuigisBoxBundle\Model\Category\CategoriesSearchResultsProvider;
use Shopsys\LuigisBoxBundle\Model\Category\LuigisBoxCategorySearchResultsMapper;
use Shopsys\LuigisBoxBundle\Model\Endpoint\LuigisBoxEndpointEnum;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class CategoriesSearchResultsProviderTest extends TestCase
{
    public function testRegularSearchLoadsCategoriesAsMainLuigisBoxType(): void
    {
        $argument = $this->createSearchArgument();
        $luigisBoxBatchLoadData = $this->createLuigisBoxSearchBatchLoadData();
        $luigisBoxResult = new LuigisBoxResult([1, 2, 3], ['category-1', 'category-2', 'category-3'], 7, []);
        $mappedCategories = [
            ['name' => 'First category'],
            ['name' => 'Second category'],
            ['name' => 'Third category'],
        ];

        $luigisBoxBatchLoadDataFactory = $this->createMock(LuigisBoxBatchLoadDataFactory::class);
        $luigisBoxBatchLoadDataFactory->expects($this->once())
            ->method('createForSearch')
            ->with(TypeInLuigisBoxEnum::CATEGORY, 3, 0, $argument)
            ->willReturn($luigisBoxBatchLoadData);

        $luigisBoxClient = $this->createMock(LuigisBoxClient::class);
        $luigisBoxClient->expects($this->once())
            ->method('getData')
            ->with($luigisBoxBatchLoadData, [TypeInLuigisBoxEnum::CATEGORY => 3])
            ->willReturn([TypeInLuigisBoxEnum::CATEGORY => $luigisBoxResult]);

        $luigisBoxCategorySearchResultsMapper = $this->createMock(LuigisBoxCategorySearchResultsMapper::class);
        $luigisBoxCategorySearchResultsMapper->expects($this->once())
            ->method('mapCategoryData')
            ->with($luigisBoxResult)
            ->willReturn($mappedCategories);

        $luigisBoxBatchLoader = $this->createMock(DataLoaderInterface::class);
        $luigisBoxBatchLoader->expects($this->never())->method('load');

        $categoriesSearchResultsProvider = new CategoriesSearchResultsProvider(
            '',
            $luigisBoxBatchLoader,
            $luigisBoxBatchLoadDataFactory,
            $luigisBoxClient,
            $luigisBoxCategorySearchResultsMapper,
        );

        $connection = $categoriesSearchResultsProvider->getCategoriesSearchResults($argument);

        self::assertSame(7, $connection->getTotalCount());
        self::assertCount(2, iterator_to_array($connection->getEdges()));
    }

    private function createSearchArgument(): Argument
    {
        return new Argument([
            'first' => 2,
            'searchInput' => [
                'search' => 'shipping',
                'isAutocomplete' => false,
                'userIdentifier' => 'user-identifier',
            ],
            'orderingMode' => null,
        ]);
    }

    private function createLuigisBoxSearchBatchLoadData(): LuigisBoxSearchBatchLoadData
    {
        return new LuigisBoxSearchBatchLoadData(
            TypeInLuigisBoxEnum::CATEGORY,
            LuigisBoxEndpointEnum::SEARCH,
            'user-identifier',
            3,
            'shipping',
            0,
        );
    }
}
