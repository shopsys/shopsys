<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Search;

use App\DataFixtures\Demo\ParameterDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Parameter\Parameter;
use Shopsys\FrameworkBundle\Model\Product\Search\AggregationResultToProductFilterCountDataTransformer;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Tests\App\Test\FunctionalTestCase;

final class SingleValueProductCountTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private FilterQueryFactory $filterQueryFactory;

    /**
     * @inject
     */
    private AggregationResultToProductFilterCountDataTransformer $countDataTransformer;

    #[DataProvider('productValuesDataProvider')]
    public function testCountsProductsInsteadOfEquivalentNumericValues(
        bool $separateProducts,
        int $expectedCount,
    ): void {
        $parameter = $this->getReference(ParameterDataFixture::PARAM_NUMBER_OF_BUTTONS, Parameter::class);
        $products = [
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2', Product::class),
        ];
        $index = $this->indexDefinitionLoader->getIndexDefinition(ProductIndex::getName(), Domain::FIRST_DOMAIN_ID)->getIndexAlias();
        $values = [
            ['parameter_id' => $parameter->getId(), 'parameter_value_id' => 900001, 'parameter_value_for_slider_filter' => 5],
            ['parameter_id' => $parameter->getId(), 'parameter_value_id' => 900002, 'parameter_value_for_slider_filter' => 5.0],
        ];
        $this->productIndexBackupFacade->createSnapshot();

        foreach ($products as $indexInProducts => $product) {
            $document = $this->elasticsearchClient->get(['index' => $index, 'id' => $product->getId()])['_source'];
            $parameters = array_values(array_filter($document['parameters'], static fn (array $value): bool => $value['parameter_id'] !== $parameter->getId()));
            $newValues = $separateProducts ? [$values[$indexInProducts]] : ($indexInProducts === 0 ? $values : []);
            $this->elasticsearchClient->update([
                'index' => $index,
                'id' => $product->getId(),
                'body' => ['doc' => ['parameters' => [...$parameters, ...$newValues]]],
                'refresh' => true,
            ]);
        }

        $query = $this->filterQueryFactory->create($index)->filterByProductIds(array_map(static fn (Product $product): int => $product->getId(), $products));
        $result = $this->elasticsearchClient->search($query->getAbsoluteNumbersWithParametersQuery());
        $countData = $this->countDataTransformer->translateAbsoluteNumbersWithParameters($result);

        $this->assertSame(2, array_sum($countData->countByParameterIdAndValueId[$parameter->getId()]));
        $this->assertSame($expectedCount, $countData->countByParameterId[$parameter->getId()]);
    }

    public static function productValuesDataProvider(): iterable
    {
        yield 'one product with equivalent values from variants' => [false, 1];

        yield 'two products with equivalent values' => [true, 2];
    }
}
