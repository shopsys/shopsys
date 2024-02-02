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
     * @param int $itemNumber
     * @param string|null $expectedImageUrl
     */
    public function testArticleFeedItemCreation(array $articleData, int $itemNumber, ?string $expectedImageUrl): void
    {
        $luigisBoxArticleFeedItemFactory = new LuigisBoxArticleFeedItemFactory();
        $luigisBoxArticleFeedItem = $luigisBoxArticleFeedItemFactory->create($articleData, $itemNumber);

        $this->assertSame($itemNumber, $luigisBoxArticleFeedItem->getSeekId());
        $this->assertSame($articleData['name'], $luigisBoxArticleFeedItem->title);
        $this->assertSame($articleData['url'], $luigisBoxArticleFeedItem->link);
        $this->assertSame($articleData['text'], $luigisBoxArticleFeedItem->description);
        $this->assertSame($expectedImageUrl, $luigisBoxArticleFeedItem->imageLink);
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
                ...$commonArticleData,
                'imageUrl' => self::ARTICLE_IMAGE_URL,
            ],
            'itemNumber' => 1,
            'expectedImageUrl' => self::ARTICLE_IMAGE_URL . '?width=605',
        ];

        yield [
            'articleData' => [
                ...$commonArticleData,
                'imageUrl' => null,
            ],
            'itemNumber' => 2,
            'expectedImageUrl' => null,
        ];

        yield [
            'articleData' => $commonArticleData,
            'itemNumber' => 3,
            'expectedImageUrl' => null,
        ];
    }
}
