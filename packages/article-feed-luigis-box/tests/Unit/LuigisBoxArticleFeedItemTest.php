<?php

declare(strict_types=1);

namespace Tests\ArticleFeed\LuigisBoxBundle\Unit;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItemFactory;
use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;

class LuigisBoxArticleFeedItemTest extends TestCase
{
    private const string ARTICLE_NAME = 'Test article';
    private const string ARTICLE_URL = 'https://www.example.com/test-article';
    private const string ARTICLE_TEXT = 'Test article text';
    private const string ARTICLE_IMAGE_URL = 'https://www.example.com/test-article.jpg';
    private const string ARTICLE_SEO_TITLE = 'Test article SEO title';
    private const string ARTICLE_SEO_META_DESCRIPTION = 'Test article SEO meta description';
    private const string ARTICLE_SEO_H1 = 'Test article heading';

    private LuigisBoxArticleFeedItemFactory $luigisBoxArticleFeedItemFactory;

    #[Override]
    protected function setUp(): void
    {
        $this->luigisBoxArticleFeedItemFactory = new LuigisBoxArticleFeedItemFactory(new ImageUrlWithSizeHelper());

        parent::setUp();
    }

    #[DataProvider('articleFeedItemCreationDataProvider')]
    public function testArticleFeedItemCreation(array $articleData): void
    {
        $luigisBoxArticleFeedItem = $this->luigisBoxArticleFeedItemFactory->create($articleData);

        $this->assertSame($articleData['index'] . '-' . $articleData['id'], $luigisBoxArticleFeedItem->getIdentity());
        $this->assertSame($articleData['name'], $luigisBoxArticleFeedItem->getName());
        $this->assertSame($articleData['url'], $luigisBoxArticleFeedItem->getUrl());
        $this->assertSame($articleData['text'], $luigisBoxArticleFeedItem->getText());
        $this->assertSame($articleData['seoTitle'] ?? null, $luigisBoxArticleFeedItem->getSeoTitle());
        $this->assertSame($articleData['seoMetaDescription'] ?? null, $luigisBoxArticleFeedItem->getSeoMetaDescription());
        $this->assertSame($articleData['seoH1'] ?? null, $luigisBoxArticleFeedItem->getSeoH1());

        $this->assertLuigisBoxCategoryFeedItemWithImageLink($articleData);
    }

    public function assertLuigisBoxCategoryFeedItemWithImageLink(array $articleData): void
    {
        if ($articleData['imageUrl'] === null) {
            return;
        }

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
                'seoTitle' => self::ARTICLE_SEO_TITLE,
                'seoMetaDescription' => self::ARTICLE_SEO_META_DESCRIPTION,
                'seoH1' => self::ARTICLE_SEO_H1,
                ...$commonArticleData,
            ],
        ];

        yield [
            'articleData' => [
                'id' => 2,
                'index' => 'article',
                'imageUrl' => null,
                'seoTitle' => self::ARTICLE_SEO_TITLE,
                'seoMetaDescription' => self::ARTICLE_SEO_META_DESCRIPTION,
                'seoH1' => null,
                ...$commonArticleData,
            ],
        ];

        yield [
            'articleData' => [
                'id' => 1,
                'index' => 'blog_article',
                'imageUrl' => null,
                'seoTitle' => null,
                'seoMetaDescription' => null,
                'seoH1' => null,
                ...$commonArticleData,
            ],
        ];
    }
}
