<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Category;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrontendApiBundle\Model\Category\CategoriesInPathFromRootBatchLoader;
use Tests\FrontendApiBundle\Test\SyncPromiseResolver;

class CategoriesInPathFromRootBatchLoaderTest extends TestCase
{
    private const int DOMAIN_ID = 1;

    private const string LOCALE = 'en';

    public function testCategoriesInPathFromRootFollowInputOrder(): void
    {
        $electronics = $this->createStub(Category::class);
        $televisions = $this->createStub(Category::class);
        $books = $this->createStub(Category::class);
        $categories = [$books, $televisions];

        $categoryFacade = $this->createMock(CategoryFacade::class);
        $categoryFacade->expects($this->once())
            ->method('getVisibleCategoriesInPathsFromRootOnDomainIndexedByCategoryId')
            ->with($categories, self::DOMAIN_ID, self::LOCALE)
            ->willReturn([
                3 => [$books],
                2 => [$electronics, $televisions],
            ]);

        $domain = $this->createStub(Domain::class);
        $domain->method('getId')->willReturn(self::DOMAIN_ID);
        $domain->method('getLocale')->willReturn(self::LOCALE);

        $loader = new CategoriesInPathFromRootBatchLoader(new SyncPromiseAdapter(), $categoryFacade, $domain);

        $result = SyncPromiseResolver::resolve($loader->loadByCategories($categories));

        $this->assertSame(
            [
                [$books],
                [$electronics, $televisions],
            ],
            $result,
        );
    }
}
