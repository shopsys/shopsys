<?php

declare(strict_types=1);

namespace Tests\LuigisBoxBundle\Unit\Model\Brand;

use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use PHPUnit\Framework\TestCase;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxSearchBatchLoadData;
use Shopsys\LuigisBoxBundle\Model\Brand\BrandSearchResultsProvider;
use Shopsys\LuigisBoxBundle\Model\Brand\LuigisBoxBrandSearchResultsMapper;
use Shopsys\LuigisBoxBundle\Model\Endpoint\LuigisBoxEndpointEnum;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;

class BrandSearchResultsProviderTest extends TestCase
{
    private const int SEARCH_LIMIT = 50;

    public function testRegularSearchLoadsBrandsAsMainLuigisBoxType(): void
    {
        $argument = $this->createSearchArgument();
        $luigisBoxBatchLoadData = $this->createLuigisBoxSearchBatchLoadData();
        $luigisBoxResult = new LuigisBoxResult([1, 2], ['brand-1', 'brand-2'], 2, []);
        $expectedBrands = [
            ['name' => 'First brand'],
            ['name' => 'Second brand'],
        ];

        $luigisBoxBatchLoadDataFactory = $this->createMock(LuigisBoxBatchLoadDataFactory::class);
        $luigisBoxBatchLoadDataFactory->expects($this->once())
            ->method('createForSearch')
            ->with(TypeInLuigisBoxEnum::BRAND, self::SEARCH_LIMIT, 0, $argument)
            ->willReturn($luigisBoxBatchLoadData);

        $luigisBoxClient = $this->createMock(LuigisBoxClient::class);
        $luigisBoxClient->expects($this->once())
            ->method('getData')
            ->with($luigisBoxBatchLoadData, [TypeInLuigisBoxEnum::BRAND => self::SEARCH_LIMIT])
            ->willReturn([TypeInLuigisBoxEnum::BRAND => $luigisBoxResult]);

        $luigisBoxBrandSearchResultsMapper = $this->createMock(LuigisBoxBrandSearchResultsMapper::class);
        $luigisBoxBrandSearchResultsMapper->expects($this->once())
            ->method('mapBrandData')
            ->with($luigisBoxResult)
            ->willReturn($expectedBrands);

        $luigisBoxBatchLoader = $this->createMock(DataLoaderInterface::class);
        $luigisBoxBatchLoader->expects($this->never())->method('load');

        $brandSearchResultsProvider = new BrandSearchResultsProvider(
            '',
            $luigisBoxBatchLoader,
            $luigisBoxBatchLoadDataFactory,
            $luigisBoxClient,
            $luigisBoxBrandSearchResultsMapper,
        );

        $actualBrands = $brandSearchResultsProvider->getBrandSearchResults($argument);

        self::assertSame($expectedBrands, $actualBrands);
    }

    private function createSearchArgument(): Argument
    {
        return new Argument([
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
            TypeInLuigisBoxEnum::BRAND,
            LuigisBoxEndpointEnum::SEARCH,
            'user-identifier',
            self::SEARCH_LIMIT,
            'shipping',
            0,
        );
    }
}
