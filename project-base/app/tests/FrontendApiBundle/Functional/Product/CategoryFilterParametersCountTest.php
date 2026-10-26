<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Category\Category;
use App\Model\Category\CategoryDataFactory;
use App\Model\Category\CategoryFacade;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class CategoryFilterParametersCountTest extends GraphQlTestCase
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
     * @inject
     */
    private ModuleFacade $moduleFacade;

    public function testExistingCategoriesDefaultToFiveParameters(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);

        $this->assertSame(5, $category->getVisibleFilterParametersCount());
        $this->assertCategoryFilterParametersCount($category, 5);
    }

    #[DataProvider('visibleParametersCountDataProvider')]
    public function testCategorySettingIsPersistedAndReturnedByGraphQl(int $count): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $categoryData = $this->categoryDataFactory->createFromCategory($category);
        $categoryData->visibleFilterParametersCount = $count;

        $this->categoryFacade->edit($category->getId(), $categoryData);
        $this->em->refresh($category);

        $this->assertSame($count, $category->getVisibleFilterParametersCount());
        $this->assertSame($count, $this->categoryDataFactory->createFromCategory($category)->visibleFilterParametersCount);
        $this->assertCategoryFilterParametersCount($category, $count);
    }

    public function testCategorySettingIsReturnedWhenFilterCountsAreDisabled(): void
    {
        $category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $categoryData = $this->categoryDataFactory->createFromCategory($category);
        $categoryData->visibleFilterParametersCount = 2;
        $this->categoryFacade->edit($category->getId(), $categoryData);
        $this->moduleFacade->setEnabled(ModuleList::PRODUCT_FILTER_COUNTS, false);

        $this->assertCategoryFilterParametersCount($category, 2);
    }

    public function testSearchUsesDefaultCountWithoutCategory(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SearchFilterParametersCount.graphql');

        $this->assertArrayNotHasKey('errors', $response);
        $this->assertSame(5, $response['data']['productsSearch']['productFilterOptions']['visibleFilterParametersCount']);
    }

    public static function visibleParametersCountDataProvider(): iterable
    {
        yield 'no inline parameters' => [0];

        yield 'two inline parameters' => [2];

        yield 'five inline parameters' => [5];

        yield 'more inline parameters' => [8];
    }

    private function assertCategoryFilterParametersCount(Category $category, int $count): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryFilterParametersCount.graphql', ['uuid' => $category->getUuid()]);

        $this->assertArrayNotHasKey('errors', $response);
        $this->assertSame($count, $response['data']['category']['products']['productFilterOptions']['visibleFilterParametersCount']);
    }
}
