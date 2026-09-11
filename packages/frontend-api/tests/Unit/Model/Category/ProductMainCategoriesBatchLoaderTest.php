<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Category;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Adapter\SyncPromiseQueue;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;
use Shopsys\FrontendApiBundle\Model\Category\CategoryFacade;
use Shopsys\FrontendApiBundle\Model\Category\ProductMainCategoriesBatchLoader;

final class ProductMainCategoriesBatchLoaderTest extends TestCase
{
    public function testLoadsCategoriesInOneBatchAndPreservesProductOrder(): void
    {
        $domainConfig = $this->createStub(DomainConfig::class);
        $domain = $this->createStub(Domain::class);
        $domain->method('getId')->willReturn(2);
        $domain->method('getCurrentDomainConfig')->willReturn($domainConfig);
        $category = $this->createStub(Category::class);
        $repository = $this->createMock(CategoryRepository::class);
        $repository->expects($this->once())->method('getProductMainCategoryIdsIndexedByProductId')
            ->with([30, 10, 20], 2)
            ->willReturn([20 => 7, 30 => 7]);
        $facade = $this->createMock(CategoryFacade::class);
        $facade->expects($this->once())->method('getVisibleCategoriesByIds')
            ->with([[7], [], [7]], $domainConfig)
            ->willReturn([[$category], [], [$category]]);
        $loader = new ProductMainCategoriesBatchLoader(new SyncPromiseAdapter(), $repository, $facade, $domain);
        $result = null;

        $loader->loadByProductIds([30, 10, 20])->then(static function ($categories) use (&$result): void {
            $result = $categories;
        });
        SyncPromiseQueue::run();

        $this->assertSame([$category, null, $category], $result);
    }
}
