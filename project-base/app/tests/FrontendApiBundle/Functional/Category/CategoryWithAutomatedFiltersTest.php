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
use Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\SpecialPricesCategoryAutomatedFilter;
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
     * @param string $categoryReferenceName
     */
    #[DataProvider('categoryWithAutomatedFiltersDataProvider')]
    public function testCategoryWithAutomatedFilters(
        array $automatedFilters,
        array $expectedProductIds,
        string $categoryReferenceName,
    ): void {
        $category = $this->getReference($categoryReferenceName, Category::class);

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

        if ($categoryReferenceName === CategoryDataFixture::CATEGORY_TOYS) {
            $this->assertSame(array_column($expectedProductsData, 'node'), $responseData['bestsellers']);
        }
    }

    /**
     * @return iterable
     */
    public static function categoryWithAutomatedFiltersDataProvider(): iterable
    {
        yield 'new products and on stock automated filters' => [
            'automatedFilters' => [NewProductsCategoryAutomatedFilter::DATABASE_VALUE, OnStockCategoryAutomatedFilter::DATABASE_VALUE],
            'expectedProductIds' => [44],
            'categoryReferenceName' => CategoryDataFixture::CATEGORY_TOYS,
        ];

        yield 'new products automated filter' => [
            'automatedFilters' => [NewProductsCategoryAutomatedFilter::DATABASE_VALUE],
            'expectedProductIds' => [44, 144],
            'categoryReferenceName' => CategoryDataFixture::CATEGORY_TOYS,
        ];

        yield 'on stock automated filter' => [
            'automatedFilters' => [OnStockCategoryAutomatedFilter::DATABASE_VALUE],
            'expectedProductIds' => [145, 44],
            'categoryReferenceName' => CategoryDataFixture::CATEGORY_TOYS,
        ];

        yield 'no automated filters' => [
            'automatedFilters' => [],
            'expectedProductIds' => [145, 44, 144, 42],
            'categoryReferenceName' => CategoryDataFixture::CATEGORY_TOYS,
        ];

        yield 'special price automated filter' => [
            'automatedFilters' => [SpecialPricesCategoryAutomatedFilter::DATABASE_VALUE],
            'expectedProductIds' => [27, 28],
            'categoryReferenceName' => CategoryDataFixture::CATEGORY_BOOKS,
        ];
    }
}
