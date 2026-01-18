<?php

declare(strict_types=1);

namespace Tests\ArticleFeed\LuigisBoxBundle\Unit;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItemFactory;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;
use Shopsys\FrameworkBundle\Model\Article\Article;

class LuigisBoxArticleFeedItemTest extends TestCase
{
    private const string ARTICLE_NAME = 'Test article';
    private const string ARTICLE_URL = 'https://www.example.com/test-article';
    private const string ARTICLE_TEXT = 'Test article text';
    private const string ARTICLE_IMAGE_URL = 'https://www.example.com/test-article.jpg';

    private LuigisBoxArticleFeedItemFactory $luigisBoxArticleFeedItemFactory;

    private Article|MockObject $defaultArticle;

    private DomainConfig $defaultDomain;

    private ImageFacade|MockObject $imageFacadeMock;

    #[Override]
    protected function setUp(): void
    {
        $this->luigisBoxArticleFeedItemFactory = new LuigisBoxArticleFeedItemFactory(new ImageUrlWithSizeHelper());
        $this->imageFacadeMock = $this->createMock(ImageFacade::class);

        $this->defaultDomain = $this->createDomainConfigMock(
            Domain::FIRST_DOMAIN_ID,
            'https://example.com',
            'en',
        );

        $this->defaultArticle = $this->createMock(Article::class);
        $this->defaultArticle->method('getName')->with('en')->willReturn(self::ARTICLE_NAME);

        parent::setUp();
    }

    private function createDomainConfigMock(int $id, string $url, string $locale): DomainConfig
    {
        $domainConfigMock = $this->createMock(DomainConfig::class);

        $domainConfigMock->method('getId')->willReturn($id);
        $domainConfigMock->method('getUrl')->willReturn($url);
        $domainConfigMock->method('getLocale')->willReturn($locale);

        return $domainConfigMock;
    }

    /**
     * @param array<string, mixed> $articleData
     */
    #[DataProvider('articleFeedItemCreationDataProvider')]
    public function testArticleFeedItemCreation(array $articleData): void
    {
        $luigisBoxArticleFeedItemFactory = new LuigisBoxArticleFeedItemFactory(new ImageUrlWithSizeHelper());
        $luigisBoxArticleFeedItem = $luigisBoxArticleFeedItemFactory->create($articleData);

        $this->assertSame($articleData['index'] . '-' . $articleData['id'], $luigisBoxArticleFeedItem->getIdentity());
        $this->assertSame($articleData['name'], $luigisBoxArticleFeedItem->getName());
        $this->assertSame($articleData['url'], $luigisBoxArticleFeedItem->getUrl());
        $this->assertSame($articleData['text'], $luigisBoxArticleFeedItem->getText());

        $this->assertLuigisBoxCategoryFeedItemWithImageLink($articleData);
    }

    private function mockImageUrl(Article $article, DomainConfig $domain, string $url): void
    {
        $this->imageFacadeMock->method('getImageUrl')
            ->with($domain, $article)->willReturn($url);
    }

    /**
     * @param array<string, mixed> $articleData
     */
    public function assertLuigisBoxCategoryFeedItemWithImageLink(array $articleData): void
    {
        if ($articleData['imageUrl'] === null) {
            return;
        }

        $this->mockImageUrl($this->defaultArticle, $this->defaultDomain, $articleData['imageUrl']);

        $luigisBoxArticleFeedItem = $this->luigisBoxArticleFeedItemFactory->create($articleData);

        self::assertEquals($articleData['imageUrl'] . '?width=100&height=100', $luigisBoxArticleFeedItem->getImageLinkS());
    }

    public static function articleFeedItemCreationDataProvider(): iterable
    {
        $commonArticleData = [
            'name' => self::ARTICLE_NAME,
            'url' => self::ARTICLE_URL,
            'text' => self::ARTICLE_TEXT,
        ];

        yield [
            'articleData' => [
                'id' => 1,
                'index' => 'article',
                'imageUrl' => self::ARTICLE_IMAGE_URL,
                ...$commonArticleData,
            ],
        ];

        yield [
            'articleData' => [
                'id' => 2,
                'index' => 'article',
                'imageUrl' => null,
                ...$commonArticleData,
            ],
        ];

        yield [
            'articleData' => [
                'id' => 1,
                'index' => 'blog_article',
                'imageUrl' => null,
                ...$commonArticleData,
            ],
        ];
    }
}
