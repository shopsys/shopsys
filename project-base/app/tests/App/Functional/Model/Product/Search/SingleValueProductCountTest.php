<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Search;

use App\DataFixtures\Demo\ParameterDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
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

    public function testSingleValueCountCountsProductsNotParameterValues(): void
    {
        $parameter = $this->getReference(ParameterDataFixture::PARAM_NUMBER_OF_BUTTONS, Parameter::class);
        $firstProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2', Product::class);
        $index = $this->indexDefinitionLoader->getIndexDefinition(ProductIndex::getName(), Domain::FIRST_DOMAIN_ID)->getIndexAlias();
        // Parameter values are unique by text, so e.g. "5" and "5.0" are distinct values with the same numeric value
        $value = ['parameter_id' => $parameter->getId(), 'parameter_value_for_slider_filter' => 5];
        $valuesByProductId = [
            $firstProduct->getId() => [
                [...$value, 'parameter_value_id' => 900001],
                [...$value, 'parameter_value_id' => 900002],
            ],
            $secondProduct->getId() => [
                [...$value, 'parameter_value_id' => 900001],
            ],
        ];
        $this->productIndexBackupFacade->createSnapshot();

        foreach ($valuesByProductId as $productId => $values) {
            $document = $this->elasticsearchClient->get(['index' => $index, 'id' => $productId])['_source'];
            $parameters = array_values(array_filter($document['parameters'], static fn (array $productValue): bool => $productValue['parameter_id'] !== $parameter->getId()));
            $this->elasticsearchClient->update([
                'index' => $index,
                'id' => $productId,
                'body' => ['doc' => ['parameters' => [...$parameters, ...$values]]],
                'refresh' => true,
            ]);
        }

        $query = $this->filterQueryFactory->create($index)->filterByProductIds(array_keys($valuesByProductId));
        $result = $this->elasticsearchClient->search($query->getAbsoluteNumbersWithParametersQuery());
        $countData = $this->countDataTransformer->translateAbsoluteNumbersWithParameters($result);

        $this->assertSame(3, array_sum($countData->countByParameterIdAndValueId[$parameter->getId()]));
        $this->assertSame(2, $countData->countByParameterId[$parameter->getId()]);
    }
}
