<?php

declare(strict_types=1);

namespace Tests\ArticleFeed\LuigisBoxBundle\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItemFactory;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Article\Article;

class LuigisBoxArticleFeedItemTest extends TestCase
{
    private const ARTICLE_NAME = 'Test article';
    private const ARTICLE_URL = 'https://www.example.com/test-article';
    private const ARTICLE_TEXT = 'Test article text';
    private const ARTICLE_IMAGE_URL = 'https://www.example.com/test-article.jpg';

    private LuigisBoxArticleFeedItemFactory $luigisBoxArticleFeedItemFactory;

    private Article|MockObject $defaultArticle;

    private DomainConfig $defaultDomain;

    private ImageFacade|MockObject $imageFacadeMock;

    protected function setUp(): void
    {
        $this->luigisBoxArticleFeedItemFactory = new LuigisBoxArticleFeedItemFactory();
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
     * @param array $articleData
     */
    #[DataProvider('articleFeedItemCreationDataProvider')]
    public function testArticleFeedItemCreation(array $articleData): void
    {
        $luigisBoxArticleFeedItemFactory = new LuigisBoxArticleFeedItemFactory();
        $luigisBoxArticleFeedItem = $luigisBoxArticleFeedItemFactory->create($articleData);

        $this->assertSame($articleData['index'] . '-' . $articleData['id'], $luigisBoxArticleFeedItem->getIdentity());
        $this->assertSame($articleData['name'], $luigisBoxArticleFeedItem->getName());
        $this->assertSame($articleData['url'], $luigisBoxArticleFeedItem->getUrl());
        $this->assertSame($articleData['text'], $luigisBoxArticleFeedItem->getText());

        $this->assertLuigisBoxCategoryFeedItemWithImageLink($articleData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\Article $article
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domain
     * @param string $url
     */
    private function mockImageUrl(Article $article, DomainConfig $domain, string $url): void
    {
        $this->imageFacadeMock->method('getImageUrl')
            ->with($domain, $article)->willReturn($url);
    }

    /**
     * @param array $articleData
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

    /**
     * @return iterable
     */
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
