<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Category;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use Overblog\DataLoader\DataLoaderInterface;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrontendApiBundle\Model\Breadcrumb\BreadcrumbLinksFactory;
use Shopsys\FrontendApiBundle\Model\Category\CategoryBreadcrumbBatchLoader;
use Tests\FrontendApiBundle\Test\SyncPromiseResolver;

class CategoryBreadcrumbBatchLoaderTest extends TestCase
{
    public function testBreadcrumbsAreCreatedFromLoadedPathsInInputOrder(): void
    {
        $electronics = $this->createStub(Category::class);
        $televisions = $this->createStub(Category::class);
        $books = $this->createStub(Category::class);
        $categories = [$televisions, $books];
        $categoriesInPaths = [
            [$electronics, $televisions],
            [$books],
        ];
        $breadcrumbs = [
            [
                ['name' => 'Electronics', 'slug' => '/electronics'],
                ['name' => 'Televisions', 'slug' => '/electronics/televisions'],
            ],
            [
                ['name' => 'Books', 'slug' => '/books'],
            ],
        ];

        $categoriesInPathFromRootBatchLoader = $this->createMock(DataLoaderInterface::class);
        $categoriesInPathFromRootBatchLoader->expects($this->once())
            ->method('loadMany')
            ->with($categories)
            ->willReturn(new SyncPromiseAdapter()->all($categoriesInPaths));

        $breadcrumbLinksFactory = $this->createMock(BreadcrumbLinksFactory::class);
        $breadcrumbLinksFactory->expects($this->once())
            ->method('createByPaths')
            ->with($categoriesInPaths, 'front_product_list')
            ->willReturn($breadcrumbs);

        $loader = new CategoryBreadcrumbBatchLoader($categoriesInPathFromRootBatchLoader, $breadcrumbLinksFactory);

        $result = SyncPromiseResolver::resolve($loader->loadBreadcrumbByCategories($categories));

        $this->assertSame($breadcrumbs, $result);
    }
}
