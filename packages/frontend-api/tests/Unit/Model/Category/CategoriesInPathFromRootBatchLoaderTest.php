<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Category;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrontendApiBundle\Model\Category\CategoriesInPathFromRootBatchLoader;

class CategoriesInPathFromRootBatchLoaderTest extends TestCase
{
    private const int DOMAIN_ID = 1;

    public function testCategoriesInPathFromRootFollowInputOrder(): void
    {
        $electronics = $this->createStub(Category::class);
        $televisions = $this->createCategoryStub(2);
        $books = $this->createCategoryStub(3);
        $categories = [$books, $televisions];

        $categoryFacade = $this->createMock(CategoryFacade::class);
        $categoryFacade->expects($this->once())
            ->method('getVisibleCategoriesInPathsFromRootOnDomainIndexedByCategoryId')
            ->with($categories, self::DOMAIN_ID)
            ->willReturn([
                2 => [$electronics, $televisions],
                3 => [$books],
            ]);

        $domain = $this->createStub(Domain::class);
        $domain->method('getId')->willReturn(self::DOMAIN_ID);

        $loader = new CategoriesInPathFromRootBatchLoader(new SyncPromiseAdapter(), $categoryFacade, $domain);

        $result = new SyncPromiseAdapter()->wait($loader->loadByCategories($categories));

        $this->assertSame(
            [
                [$books],
                [$electronics, $televisions],
            ],
            $result,
        );
    }

    private function createCategoryStub(int $id): Category
    {
        $category = $this->createStub(Category::class);
        $category->method('getId')->willReturn($id);

        return $category;
    }
}
