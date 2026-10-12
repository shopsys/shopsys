<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Blog\Category;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\BlogArticleExportQueueFacade;
use Shopsys\FrameworkBundle\Model\Blog\BlogVisibilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFactory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryRepository;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryWithPreloadedChildrenFactory;

class BlogCategoryFacadeTest extends TestCase
{
    private const int FIRST_DOMAIN_ID = 1;

    private const int SECOND_DOMAIN_ID = 2;

    public function testVisibleChildrenWithRootAreLoadedOncePerDomain(): void
    {
        $firstDomainRoot = $this->createStub(BlogCategory::class);
        $secondDomainRoot = $this->createStub(BlogCategory::class);

        $blogCategoryRepository = $this->createMock(BlogCategoryRepository::class);
        $blogCategoryRepository->expects($this->exactly(2))
            ->method('getAllVisibleChildrenWithRootByDomainId')
            ->willReturnMap([
                [self::FIRST_DOMAIN_ID, [$firstDomainRoot]],
                [self::SECOND_DOMAIN_ID, [$secondDomainRoot]],
            ]);

        $blogCategoryFacade = $this->createBlogCategoryFacade($blogCategoryRepository);

        $this->assertSame([$firstDomainRoot], $blogCategoryFacade->getAllVisibleChildrenWithRootByDomainId(self::FIRST_DOMAIN_ID));
        $this->assertSame([$firstDomainRoot], $blogCategoryFacade->getAllVisibleChildrenWithRootByDomainId(self::FIRST_DOMAIN_ID));
        $this->assertSame([$secondDomainRoot], $blogCategoryFacade->getAllVisibleChildrenWithRootByDomainId(self::SECOND_DOMAIN_ID));
        $this->assertSame([$secondDomainRoot], $blogCategoryFacade->getAllVisibleChildrenWithRootByDomainId(self::SECOND_DOMAIN_ID));
    }

    public function testVisibleChildrenWithRootAreReloadedAfterBlogCategoryIsDeleted(): void
    {
        $rootBeforeDeletion = $this->createStub(BlogCategory::class);
        $rootAfterDeletion = $this->createStub(BlogCategory::class);
        $deletedBlogCategory = $this->createStub(BlogCategory::class);
        $deletedBlogCategory->method('getChildren')->willReturn([]);

        $blogCategoryRepository = $this->createMock(BlogCategoryRepository::class);
        $blogCategoryRepository->method('getById')->willReturn($deletedBlogCategory);
        $blogCategoryRepository->expects($this->exactly(2))
            ->method('getAllVisibleChildrenWithRootByDomainId')
            ->with(self::FIRST_DOMAIN_ID)
            ->willReturnOnConsecutiveCalls([$rootBeforeDeletion], [$rootAfterDeletion]);

        $blogCategoryFacade = $this->createBlogCategoryFacade($blogCategoryRepository);

        $this->assertSame([$rootBeforeDeletion], $blogCategoryFacade->getAllVisibleChildrenWithRootByDomainId(self::FIRST_DOMAIN_ID));
        $this->assertSame([$rootBeforeDeletion], $blogCategoryFacade->getAllVisibleChildrenWithRootByDomainId(self::FIRST_DOMAIN_ID));

        $blogCategoryFacade->deleteById(5);

        $this->assertSame([$rootAfterDeletion], $blogCategoryFacade->getAllVisibleChildrenWithRootByDomainId(self::FIRST_DOMAIN_ID));
    }

    private function createBlogCategoryFacade(
        BlogCategoryRepository|MockObject $blogCategoryRepository,
    ): BlogCategoryFacade {
        return new BlogCategoryFacade(
            $this->createStub(EntityManagerInterface::class),
            $blogCategoryRepository,
            $this->createStub(FriendlyUrlFacade::class),
            $this->createStub(ImageFacade::class),
            $this->createStub(BlogCategoryFactory::class),
            $this->createStub(BlogCategoryWithPreloadedChildrenFactory::class),
            $this->createStub(BlogVisibilityRecalculationScheduler::class),
            $this->createStub(BlogArticleExportQueueFacade::class),
            $this->createStub(BlogArticleFacade::class),
            $this->createStub(Domain::class),
            $this->createStub(CleanStorefrontCacheFacade::class),
            new InMemoryCache(),
        );
    }
}
