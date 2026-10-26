<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Resolver\Category;

use ArrayObject;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\DataLoader\DataLoaderInterface;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Category\CategoryResolverMap;

class CategoryResolverMapTest extends TestCase
{
    public function testBreadcrumbOfCategoryIsLoadedByBreadcrumbBatchLoader(): void
    {
        $category = $this->createStub(Category::class);
        $breadcrumbPromise = $this->createStub(Promise::class);
        $categoryBreadcrumbBatchLoader = $this->createMock(DataLoaderInterface::class);
        $categoryBreadcrumbBatchLoader->expects($this->once())
            ->method('load')
            ->with($category)
            ->willReturn($breadcrumbPromise);
        $categoryResolverMap = $this->createCategoryResolverMap($categoryBreadcrumbBatchLoader);

        $result = $this->resolveCategoryField($categoryResolverMap, 'breadcrumb', $category);

        $this->assertSame($breadcrumbPromise, $result);
    }

    public function testBreadcrumbOfReadyCategorySeoMixIsLoadedByBreadcrumbBatchLoaderForItsCategory(): void
    {
        $category = $this->createStub(Category::class);
        $readyCategorySeoMix = $this->createStub(ReadyCategorySeoMix::class);
        $readyCategorySeoMix->method('getCategory')->willReturn($category);
        $breadcrumbPromise = $this->createStub(Promise::class);
        $categoryBreadcrumbBatchLoader = $this->createMock(DataLoaderInterface::class);
        $categoryBreadcrumbBatchLoader->expects($this->once())
            ->method('load')
            ->with($category)
            ->willReturn($breadcrumbPromise);
        $categoryResolverMap = $this->createCategoryResolverMap($categoryBreadcrumbBatchLoader);

        $result = $this->resolveCategoryField($categoryResolverMap, 'breadcrumb', $readyCategorySeoMix);

        $this->assertSame($breadcrumbPromise, $result);
    }

    private function createCategoryResolverMap(
        DataLoaderInterface $categoryBreadcrumbBatchLoader,
    ): CategoryResolverMap {
        return new CategoryResolverMap(
            $this->createStub(Domain::class),
            $this->createStub(HreflangLinksFacade::class),
            $this->createStub(DataLoaderInterface::class),
            $this->createStub(DataLoaderInterface::class),
            $this->createStub(DataLoaderInterface::class),
            $this->createStub(DataLoaderInterface::class),
            $categoryBreadcrumbBatchLoader,
            $this->createStub(DataLoaderInterface::class),
        );
    }

    private function resolveCategoryField(
        CategoryResolverMap $categoryResolverMap,
        string $fieldName,
        Category|ReadyCategorySeoMix $value,
    ): mixed {
        $resolveInfo = $this->createStub(ResolveInfo::class);
        $resolveInfo->fieldName = $fieldName;
        $resolveField = $categoryResolverMap->resolve('Category', ResolverMap::RESOLVE_FIELD);

        return $resolveField($value, new Argument(), new ArrayObject(), $resolveInfo);
    }
}
