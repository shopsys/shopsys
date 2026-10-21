<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Category;

use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\ProductListFriendlyUrlDataProvider;
use Shopsys\FrontendApiBundle\Model\Category\CategoryBreadcrumbLinksFactory;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlSlugBatchLoader;

class CategoryBreadcrumbLinksFactoryTest extends TestCase
{
    private const string LOCALE = 'en';

    private FriendlyUrlSlugBatchLoader|MockObject $friendlyUrlSlugBatchLoader;

    private CategoryBreadcrumbLinksFactory $categoryBreadcrumbLinksFactory;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->friendlyUrlSlugBatchLoader = $this->createMock(FriendlyUrlSlugBatchLoader::class);
        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getLocale')->willReturn(self::LOCALE);

        $this->categoryBreadcrumbLinksFactory = new CategoryBreadcrumbLinksFactory($this->friendlyUrlSlugBatchLoader, $domainStub);
    }

    public function testBreadcrumbLinksFollowPathOrderAndMapNamesWithSlugs(): void
    {
        $electronics = $this->createCategoryStub(1, 'Electronics');
        $televisions = $this->createCategoryStub(2, 'Televisions');
        $books = $this->createCategoryStub(3, 'Books');

        $this->friendlyUrlSlugBatchLoader->expects($this->once())
            ->method('getSlugsIndexedByEntityId')
            ->with([1, 2, 3], ProductListFriendlyUrlDataProvider::ROUTE_NAME)
            ->willReturn([
                1 => '/electronics',
                2 => '/electronics/televisions',
                3 => '/books',
            ]);

        $result = $this->categoryBreadcrumbLinksFactory->createByPaths(
            [
                [$electronics, $televisions],
                [$books],
            ],
            ProductListFriendlyUrlDataProvider::ROUTE_NAME,
        );

        $this->assertSame(
            [
                [
                    ['name' => 'Electronics', 'slug' => '/electronics'],
                    ['name' => 'Televisions', 'slug' => '/electronics/televisions'],
                ],
                [
                    ['name' => 'Books', 'slug' => '/books'],
                ],
            ],
            $result,
        );
    }

    public function testSharedAncestorSlugsAreRequestedOnce(): void
    {
        $electronics = $this->createCategoryStub(1, 'Electronics');
        $televisions = $this->createCategoryStub(2, 'Televisions');
        $audio = $this->createCategoryStub(4, 'Audio');

        $this->friendlyUrlSlugBatchLoader->expects($this->once())
            ->method('getSlugsIndexedByEntityId')
            ->with([1, 2, 4], ProductListFriendlyUrlDataProvider::ROUTE_NAME)
            ->willReturn([
                1 => '/electronics',
                2 => '/electronics/televisions',
                4 => '/electronics/audio',
            ]);

        $result = $this->categoryBreadcrumbLinksFactory->createByPaths(
            [
                [$electronics, $televisions],
                [$electronics, $audio],
            ],
            ProductListFriendlyUrlDataProvider::ROUTE_NAME,
        );

        $this->assertSame(
            [
                [
                    ['name' => 'Electronics', 'slug' => '/electronics'],
                    ['name' => 'Televisions', 'slug' => '/electronics/televisions'],
                ],
                [
                    ['name' => 'Electronics', 'slug' => '/electronics'],
                    ['name' => 'Audio', 'slug' => '/electronics/audio'],
                ],
            ],
            $result,
        );
    }

    private function createCategoryStub(int $id, string $name): Category
    {
        $category = $this->createStub(Category::class);
        $category->method('getId')->willReturn($id);
        $category->method('getName')->willReturn($name);

        return $category;
    }
}
