<?php

declare(strict_types=1);

namespace Tests\ArticleFeed\LuigisBoxBundle\Unit;

use PHPUnit\Framework\TestCase;
use Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItemFactory;

class LuigisBoxArticleFeedItemTest extends TestCase
{
    private const ARTICLE_NAME = 'Test article';
    private const ARTICLE_URL = 'https://www.example.com/test-article';
    private const ARTICLE_TEXT = 'Test article text';
    private const ARTICLE_IMAGE_URL = 'https://www.example.com/test-article.jpg';

    /**
     * @dataProvider articleFeedItemCreationDataProvider
     * @param array $articleData
     */
    public function testArticleFeedItemCreation(array $articleData): void
    {
        $luigisBoxArticleFeedItemFactory = new LuigisBoxArticleFeedItemFactory();
        $luigisBoxArticleFeedItem = $luigisBoxArticleFeedItemFactory->create($articleData);

        $this->assertSame($articleData['index'] . '-' . $articleData['id'], $luigisBoxArticleFeedItem->getIdentity());
        $this->assertSame($articleData['name'], $luigisBoxArticleFeedItem->getName());
        $this->assertSame($articleData['url'], $luigisBoxArticleFeedItem->getUrl());
        $this->assertSame($articleData['text'], $luigisBoxArticleFeedItem->getText());
    }

    /**
     * @return iterable
     */
    public function articleFeedItemCreationDataProvider(): iterable
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
                ...$commonArticleData,
                'imageUrl' => self::ARTICLE_IMAGE_URL,
            ],
            'expectedImageUrl' => self::ARTICLE_IMAGE_URL . '?width=605',
        ];

        yield [
            'articleData' => [
                'id' => 2,
                'index' => 'article',
                ...$commonArticleData,
                'imageUrl' => null,
            ],
            'itemNumber' => 2,
            'expectedImageUrl' => null,
        ];

        yield [
            'articleData' => [
                'id' => 1,
                'index' => 'blog_article',
                ...$commonArticleData,
            ],
            'expectedImageUrl' => null,
        ];
    }
}
