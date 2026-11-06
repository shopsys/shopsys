<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Product\Filter;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterFacade;
use Shopsys\FrameworkBundle\Model\Product\Parameter\ParameterValue;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper;

class ProductFilterDataMapperTest extends TestCase
{
    private const string BRAND_UUID = 'b1a3c9a0-0000-4000-8000-000000000001';

    private const string FLAG_UUID = 'f1a3c9a0-0000-4000-8000-000000000001';

    private const string PARAMETER_UUID = 'a1a3c9a0-0000-4000-8000-000000000001';

    private const string PARAMETER_VALUE_UUID = 'c1a3c9a0-0000-4000-8000-000000000001';

    private const array FRONTEND_API_FILTER = [
        'brands' => [self::BRAND_UUID],
        'flags' => [self::FLAG_UUID],
        'parameters' => [
            [
                'parameter' => self::PARAMETER_UUID,
                'values' => [self::PARAMETER_VALUE_UUID],
                'minimalValue' => null,
                'maximalValue' => null,
            ],
        ],
    ];

    public function testEntitiesOfTheSameFilterAreLoadedOnlyOnce(): void
    {
        $brand = $this->createStub(Brand::class);
        $flag = $this->createStub(Flag::class);
        $parameter = $this->createStub(Parameter::class);
        $parameterValue = $this->createStub(ParameterValue::class);

        $brandFacade = $this->createMock(BrandFacade::class);
        $brandFacade->expects($this->once())->method('getByUuids')->with([self::BRAND_UUID])->willReturn([$brand]);

        $flagFacade = $this->createMock(FlagFacade::class);
        $flagFacade->expects($this->once())->method('getByUuids')->with([self::FLAG_UUID])->willReturn([$flag]);

        $parameterFacade = $this->createMock(ParameterFacade::class);
        $parameterFacade->expects($this->once())->method('getParametersByUuids')
            ->with([self::PARAMETER_UUID])
            ->willReturn([self::PARAMETER_UUID => $parameter]);
        $parameterFacade->expects($this->once())->method('getParameterValuesByUuids')
            ->with([self::PARAMETER_VALUE_UUID])
            ->willReturn([self::PARAMETER_VALUE_UUID => $parameterValue]);

        $productFilterDataMapper = new ProductFilterDataMapper(
            $flagFacade,
            $brandFacade,
            $parameterFacade,
            new ProductFilterDataFactory(),
            new InMemoryCache(),
        );

        $productFilterDataMapper->mapFrontendApiFilterToProductFilterData(self::FRONTEND_API_FILTER);
        $productFilterData = $productFilterDataMapper->mapFrontendApiFilterToProductFilterData(self::FRONTEND_API_FILTER);

        $this->assertSame([$brand], $productFilterData->brands);
        $this->assertSame([$flag], $productFilterData->flags);
        $this->assertSame($parameter, $productFilterData->parameters[0]->parameter);
        $this->assertSame([$parameterValue], $productFilterData->parameters[0]->values);
    }

    public function testEntitiesOfDifferentFiltersAreLoadedSeparately(): void
    {
        $otherFlagUuid = 'f1a3c9a0-0000-4000-8000-000000000002';

        $flagFacade = $this->createMock(FlagFacade::class);
        $flagFacade->expects($this->exactly(2))->method('getByUuids')
            ->willReturnCallback(fn (array $uuids): array => array_map(
                fn (): Flag => $this->createStub(Flag::class),
                $uuids,
            ));

        $productFilterDataMapper = new ProductFilterDataMapper(
            $flagFacade,
            $this->createStub(BrandFacade::class),
            $this->createStub(ParameterFacade::class),
            new ProductFilterDataFactory(),
            new InMemoryCache(),
        );

        $productFilterData = $productFilterDataMapper->mapFrontendApiFilterToProductFilterData(['flags' => [self::FLAG_UUID]]);
        $otherProductFilterData = $productFilterDataMapper->mapFrontendApiFilterToProductFilterData(['flags' => [self::FLAG_UUID, $otherFlagUuid]]);

        $this->assertCount(1, $productFilterData->flags);
        $this->assertCount(2, $otherProductFilterData->flags);
    }
}
