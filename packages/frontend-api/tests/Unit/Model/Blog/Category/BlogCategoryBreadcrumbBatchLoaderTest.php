<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Blog\Category;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDetailFriendlyUrlDataProvider;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrontendApiBundle\Model\Blog\Category\BlogCategoryBreadcrumbBatchLoader;
use Shopsys\FrontendApiBundle\Model\Category\CategoryBreadcrumbLinksFactory;

class BlogCategoryBreadcrumbBatchLoaderTest extends TestCase
{
    private const int DOMAIN_ID = 1;

    public function testBreadcrumbsAreCreatedFromVisiblePathsInInputOrder(): void
    {
        $root = $this->createStub(BlogCategory::class);
        $news = $this->createBlogCategoryStub(2);
        $tips = $this->createBlogCategoryStub(3);
        $blogCategories = [$tips, $news];
        $breadcrumbs = [
            [
                ['name' => 'Blog', 'slug' => '/blog'],
                ['name' => 'Tips', 'slug' => '/blog/tips'],
            ],
            [
                ['name' => 'Blog', 'slug' => '/blog'],
                ['name' => 'News', 'slug' => '/blog/news'],
            ],
        ];

        $blogCategoryFacade = $this->createMock(BlogCategoryFacade::class);
        $blogCategoryFacade->expects($this->once())
            ->method('getVisibleBlogCategoriesInPathsFromRootOnDomainIndexedByBlogCategoryId')
            ->with($blogCategories, self::DOMAIN_ID)
            ->willReturn([
                2 => [$root, $news],
                3 => [$root, $tips],
            ]);

        $breadcrumbLinksFactory = $this->createMock(CategoryBreadcrumbLinksFactory::class);
        $breadcrumbLinksFactory->expects($this->once())
            ->method('createByPaths')
            ->with([[$root, $tips], [$root, $news]], BlogCategoryDetailFriendlyUrlDataProvider::ROUTE_NAME)
            ->willReturn($breadcrumbs);

        $domain = $this->createStub(Domain::class);
        $domain->method('getId')->willReturn(self::DOMAIN_ID);

        $loader = new BlogCategoryBreadcrumbBatchLoader(
            new SyncPromiseAdapter(),
            $blogCategoryFacade,
            $breadcrumbLinksFactory,
            $domain,
        );

        $result = new SyncPromiseAdapter()->wait($loader->loadBreadcrumbByBlogCategories($blogCategories));

        $this->assertSame($breadcrumbs, $result);
    }

    private function createBlogCategoryStub(int $id): BlogCategory
    {
        $blogCategory = $this->createStub(BlogCategory::class);
        $blogCategory->method('getId')->willReturn($id);

        return $blogCategory;
    }
}
