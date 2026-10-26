<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\BrandDataFixture;
use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\ParameterDataFixture;
use App\Model\Category\Category;
use App\Model\Product\Brand\Brand;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class SingleNumericValueCountTest extends GraphQlTestCase
{
    #[DataProvider('filterDataProvider')]
    public function testSingleValueCountRespectsOtherFilters(
        ?string $brandReference,
        bool $selected,
        int $expectedCount,
    ): void {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $parameter = $this->getReference(ParameterDataFixture::PARAM_NUMBER_OF_BUTTONS, Parameter::class);
        $filter = [];

        if ($brandReference !== null) {
            $filter['brands'] = [$this->getReference($brandReference, Brand::class)->getUuid()];
        }

        if ($selected) {
            $filter['parameters'] = [['parameter' => $parameter->getUuid(), 'minimalValue' => 5, 'maximalValue' => 5]];
        }

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SingleNumericValueCount.graphql', [
            'uuid' => $category->getUuid(),
            'filter' => $filter === [] ? null : $filter,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'category');
        $parameters = array_column($data['products']['productFilterOptions']['parameters'], null, 'uuid');

        $this->assertSame(5.0, $parameters[$parameter->getUuid()]['minimalValue']);
        $this->assertSame(5.0, $parameters[$parameter->getUuid()]['maximalValue']);
        $this->assertSame($expectedCount, $parameters[$parameter->getUuid()]['singleValueCount']);
    }

    public static function filterDataProvider(): iterable
    {
        yield 'unfiltered' => [null, false, 1];

        yield 'matching brand' => [BrandDataFixture::BRAND_A4TECH, false, 1];

        yield 'excluding brand' => [BrandDataFixture::BRAND_LG, false, 0];

        yield 'selected value' => [null, true, 1];
    }
}
