<?php

declare(strict_types=1);

namespace Tests\CategoryFeed\LuigisBoxBundle\Unit;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxCategoryFeedItemFactory;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\Exception\ImageNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Category\CategoryRepository;

class LuigisBoxCategoryFeedItemTest extends TestCase
{
    private const CATEGORY_NAME = 'category name';

    private const CATEGORY_ID = 1;

    private const CATEGORY_URL = 'https://example.com/category-1';

    private const CATEGORY_DESCRIPTION = 'category description';

    private const CATEGORY_IMAGE_URL = 'https://example.com/img/category/1';

    private LuigisBoxCategoryFeedItemFactory $luigisBoxFeedItemFactory;

    private DomainConfig $defaultDomain;

    private Category|MockObject $defaultCategory;

    private FriendlyUrlFacade|MockObject $friendlyUrlFacadeMock;

    private ImageFacade|MockObject $imageFacadeMock;

    protected function setUp(): void
    {
        $this->friendlyUrlFacadeMock = $this->createMock(FriendlyUrlFacade::class);
        $this->imageFacadeMock = $this->createMock(ImageFacade::class);
        $categoryRepositoryMock = $this->createMock(CategoryRepository::class);

        $this->luigisBoxFeedItemFactory = new LuigisBoxCategoryFeedItemFactory(
            $this->friendlyUrlFacadeMock,
            $this->imageFacadeMock,
            $categoryRepositoryMock,
        );

        $this->defaultDomain = $this->createDomainConfigMock(
            Domain::FIRST_DOMAIN_ID,
            'https://example.com',
            'en',
        );

        $this->defaultCategory = $this->createMock(Category::class);
        $this->defaultCategory->method('getId')->willReturn(self::CATEGORY_ID);
        $this->defaultCategory->method('getName')->with('en')->willReturn(self::CATEGORY_NAME);

        $this->mockCategoryUrl($this->defaultCategory, $this->defaultDomain, self::CATEGORY_URL);
    }

    /**
     * @param int $id
     * @param string $url
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    private function createDomainConfigMock(int $id, string $url, string $locale): DomainConfig
    {
        $domainConfigMock = $this->createMock(DomainConfig::class);

        $domainConfigMock->method('getId')->willReturn($id);
        $domainConfigMock->method('getUrl')->willReturn($url);
        $domainConfigMock->method('getLocale')->willReturn($locale);

        return $domainConfigMock;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domain
     * @param string $url
     */
    private function mockCategoryUrl(Category $category, DomainConfig $domain, string $url): void
    {
        $this->friendlyUrlFacadeMock->method('getAbsoluteUrlByRouteNameAndEntityId')
            ->with($domain->getId(), 'front_product_list', $category->getId())->willReturn($url);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domain
     * @param string $url
     */
    private function mockCategoryImageUrl(Category $category, DomainConfig $domain, string $url): void
    {
        $this->imageFacadeMock->method('getImageUrl')
            ->with($domain, $category)->willReturn($url);
    }

    public function testMinimalLuigisBoxCategoryFeedItemIsCreatable(): void
    {
        $this->imageFacadeMock->method('getImageUrl')->willThrowException(new ImageNotFoundException());

        $luigisBoxCategoryFeedItem = $this->luigisBoxFeedItemFactory->create($this->defaultCategory, $this->defaultDomain);

        self::assertEquals(self::CATEGORY_ID, $luigisBoxCategoryFeedItem->getId());
        self::assertEquals(self::CATEGORY_ID, $luigisBoxCategoryFeedItem->getSeekId());
        self::assertEquals(self::CATEGORY_NAME, $luigisBoxCategoryFeedItem->getTitle());
        self::assertNull($luigisBoxCategoryFeedItem->getDescription());
        self::assertEquals(self::CATEGORY_URL, $luigisBoxCategoryFeedItem->getLink());
        self::assertNull($luigisBoxCategoryFeedItem->getImageLink());
        self::assertEquals('1', $luigisBoxCategoryFeedItem->getHierarchyIds());
        self::assertEquals(self::CATEGORY_NAME, $luigisBoxCategoryFeedItem->getHierarchyText());
    }

    public function testLuigisBoxCategoryFeedItemWithDescription(): void
    {
        $this->imageFacadeMock->method('getImageUrl')->willThrowException(new ImageNotFoundException());
        $this->defaultCategory->method('getDescription')->with(Domain::FIRST_DOMAIN_ID)->willReturn(self::CATEGORY_DESCRIPTION);

        $luigisBoxCategoryFeedItem = $this->luigisBoxFeedItemFactory->create($this->defaultCategory, $this->defaultDomain);

        self::assertEquals(self::CATEGORY_DESCRIPTION, $luigisBoxCategoryFeedItem->getDescription());
    }

    public function testLuigisBoxCategoryFeedItemWithImageLink(): void
    {
        $this->mockCategoryImageUrl($this->defaultCategory, $this->defaultDomain, self::CATEGORY_IMAGE_URL);

        $luigisBoxCategoryFeedItem = $this->luigisBoxFeedItemFactory->create($this->defaultCategory, $this->defaultDomain);

        self::assertEquals(self::CATEGORY_IMAGE_URL . '?width=605', $luigisBoxCategoryFeedItem->getImageLink());
    }
}
