<?php

declare(strict_types=1);

namespace Tests\CategoryFeed\LuigisBoxBundle\Unit;

use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxCategoryFeedItemFactory;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;

class LuigisBoxCategoryFeedItemTest extends TestCase
{
    private const string CATEGORY_NAME = 'category name';
    private const int CATEGORY_ID = 1;
    private const string CATEGORY_IDENTITY = 'category-1';
    private const string CATEGORY_URL = 'https://example.com/category-1';
    private const string CATEGORY_IMAGE_URL = 'https://example.com/img/category/1';

    private LuigisBoxCategoryFeedItemFactory $luigisBoxCategoryFeedItemFactory;

    private DomainConfig $defaultDomain;

    private Stub|Category $defaultCategoryStub;

    private Stub|FriendlyUrlFacade $friendlyUrlFacadeStub;

    private Stub|ImageFacade $imageFacadeStub;

    #[Override]
    protected function setUp(): void
    {
        $this->friendlyUrlFacadeStub = $this->createStub(FriendlyUrlFacade::class);
        $this->imageFacadeStub = $this->createStub(ImageFacade::class);
        $categoryRepositoryStub = $this->createStub(CategoryRepository::class);

        $this->luigisBoxCategoryFeedItemFactory = new LuigisBoxCategoryFeedItemFactory(
            $this->friendlyUrlFacadeStub,
            $this->imageFacadeStub,
            $categoryRepositoryStub,
            new ImageUrlWithSizeHelper(),
        );

        $this->defaultDomain = $this->createDomainConfigStub(
            Domain::FIRST_DOMAIN_ID,
            'https://example.com',
            'en',
        );

        $this->defaultCategoryStub = $this->createStub(Category::class);
        $this->defaultCategoryStub->method('getId')->willReturn(self::CATEGORY_ID);
        $this->defaultCategoryStub->method('getName')->willReturnMap([['en', self::CATEGORY_NAME]]);

        $this->stubCategoryUrl($this->defaultCategoryStub, $this->defaultDomain, self::CATEGORY_URL);
    }

    private function createDomainConfigStub(int $id, string $url, string $locale): DomainConfig
    {
        $domainConfigStub = $this->createStub(DomainConfig::class);

        $domainConfigStub->method('getId')->willReturn($id);
        $domainConfigStub->method('getUrl')->willReturn($url);
        $domainConfigStub->method('getLocale')->willReturn($locale);

        return $domainConfigStub;
    }

    private function stubCategoryUrl(Category $category, DomainConfig $domain, string $url): void
    {
        $this->friendlyUrlFacadeStub->method('getAbsoluteUrlByRouteNameAndEntityId')
            ->willReturnMap([
                [$domain->getId(), 'front_product_list', $category->getId(), $url],
            ]);
    }

    private function stubCategoryImageUrl(Category $category, DomainConfig $domain, string $url): void
    {
        $this->imageFacadeStub->method('getImageUrl')
            ->willReturnMap([
                [$domain, $category, $url],
            ]);
    }

    public function testMinimalLuigisBoxCategoryFeedItemIsCreatable(): void
    {
        $this->imageFacadeStub->method('getImageUrl')->willThrowException(new ImageNotFoundException());

        $luigisBoxCategoryFeedItem = $this->luigisBoxCategoryFeedItemFactory->create($this->defaultCategoryStub, $this->defaultDomain);

        self::assertEquals(self::CATEGORY_IDENTITY, $luigisBoxCategoryFeedItem->getIdentity());
        self::assertEquals(self::CATEGORY_ID, $luigisBoxCategoryFeedItem->getSeekId());
        self::assertEquals(self::CATEGORY_NAME, $luigisBoxCategoryFeedItem->getName());
        self::assertEquals(self::CATEGORY_URL, $luigisBoxCategoryFeedItem->getUrl());
        self::assertNull($luigisBoxCategoryFeedItem->getImageUrl());
        self::assertEquals(null, $luigisBoxCategoryFeedItem->getHierarchy());
    }

    public function testLuigisBoxCategoryFeedItemWithImageLink(): void
    {
        $this->stubCategoryImageUrl($this->defaultCategoryStub, $this->defaultDomain, self::CATEGORY_IMAGE_URL);

        $luigisBoxCategoryFeedItem = $this->luigisBoxCategoryFeedItemFactory->create($this->defaultCategoryStub, $this->defaultDomain);

        self::assertEquals(self::CATEGORY_IMAGE_URL . '?width=100&height=100', $luigisBoxCategoryFeedItem->getImageUrl());
    }
}
