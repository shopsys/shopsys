<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Blog\Category;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrontendApiBundle\Model\Blog\Category\BlogCategoryBreadcrumbBatchLoader;
use Shopsys\FrontendApiBundle\Model\Breadcrumb\BreadcrumbLinksFactory;
use Tests\FrontendApiBundle\Test\SyncPromiseResolver;

class BlogCategoryBreadcrumbBatchLoaderTest extends TestCase
{
    private const int DOMAIN_ID = 1;

    private const string LOCALE = 'en';

    public function testBreadcrumbsAreCreatedFromVisiblePathsInInputOrder(): void
    {
        $root = $this->createStub(BlogCategory::class);
        $news = $this->createStub(BlogCategory::class);
        $tips = $this->createStub(BlogCategory::class);
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
            ->with($blogCategories, self::DOMAIN_ID, self::LOCALE)
            ->willReturn([
                3 => [$root, $tips],
                2 => [$root, $news],
            ]);

        $breadcrumbLinksFactory = $this->createMock(BreadcrumbLinksFactory::class);
        $breadcrumbLinksFactory->expects($this->once())
            ->method('createByPaths')
            ->with([[$root, $tips], [$root, $news]], 'front_blogcategory_detail')
            ->willReturn($breadcrumbs);

        $domain = $this->createStub(Domain::class);
        $domain->method('getId')->willReturn(self::DOMAIN_ID);
        $domain->method('getLocale')->willReturn(self::LOCALE);

        $loader = new BlogCategoryBreadcrumbBatchLoader(
            new SyncPromiseAdapter(),
            $blogCategoryFacade,
            $breadcrumbLinksFactory,
            $domain,
        );

        $result = SyncPromiseResolver::resolve($loader->loadBreadcrumbByBlogCategories($blogCategories));

        $this->assertSame($breadcrumbs, $result);
    }
}
