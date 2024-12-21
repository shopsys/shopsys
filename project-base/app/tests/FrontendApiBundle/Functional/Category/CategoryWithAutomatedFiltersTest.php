<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Category\Category;
use App\Model\Category\CategoryDataFactory;
use App\Model\Category\CategoryFacade;
use App\Model\Product\Product;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\NewProductsCategoryAutomatedFilter;
use Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\OnStockCategoryAutomatedFilter;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CategoryWithAutomatedFiltersTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private CategoryDataFactory $categoryDataFactory;

    /**
     * @inject
     */
    private CategoryFacade $categoryFacade;

    /**
     * @param string[] $automatedFilters
     * @param int[] $expectedProductIds
     */
    #[DataProvider('categoryWithAutomatedFiltersDataProvider')]
    public function testCategoryWithAutomatedFilters(array $automatedFilters, array $expectedProductIds): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_TOYS, Category::class);

        $categoryData = $this->categoryDataFactory->createFromCategory($category);
        $categoryData->automatedFilters = $automatedFilters;
        $this->categoryFacade->edit($category->getId(), $categoryData);

        $locale = $this->getLocaleForFirstDomain();

        $expectedProductsData = [];

        foreach ($expectedProductIds as $expectedProductId) {
            $expectedProductsData[] = [
                'node' => [
                    'name' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $expectedProductId, Product::class)->getName($locale),
                ],
            ];
        }

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryQuery.graphql', [
            'categoryUuid' => $category->getUuid(),
            'firstProducts' => 10,
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'category');

        $this->assertSame($automatedFilters, $responseData['automatedFilters']);
        $this->assertSame($expectedProductsData, $responseData['products']['edges']);
    }

    /**
     * @return iterable
     */
    public static function categoryWithAutomatedFiltersDataProvider(): iterable
    {
        yield 'new products and on stock automated filters' => [
            [NewProductsCategoryAutomatedFilter::DATABASE_VALUE, OnStockCategoryAutomatedFilter::DATABASE_VALUE],
            [44],
        ];

        yield 'new products automated filter' => [
            [NewProductsCategoryAutomatedFilter::DATABASE_VALUE],
            [44, 144],
        ];

        yield 'on stock automated filter' => [
            [OnStockCategoryAutomatedFilter::DATABASE_VALUE],
            [145, 44],
        ];

        yield 'no automated filters' => [
            [],
            [145, 44, 144, 42],
        ];
    }
}
