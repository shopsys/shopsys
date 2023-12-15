<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\PersooBundle\Model;

use Shopsys\FrameworkBundle\Model\Feed\FeedItemImageHelper;

class PersooArticleFeedItemFactory
{
    /**
     * @param array $articleData
     * @param int $itemNumber
     * @return \Shopsys\ArticleFeed\PersooBundle\Model\PersooArticleFeedItem
     */
    public function create(array $articleData, int $itemNumber): PersooArticleFeedItem
    {
        return new PersooArticleFeedItem(
            $itemNumber,
            $articleData['name'],
            $articleData['url'],
            $articleData['text'],
            $this->getImageUrl($articleData),
        );
    }

    /**
     * @param array $articleData
     * @return string|null
     */
    protected function getImageUrl(array $articleData): ?string
    {
        if (!array_key_exists('imageUrl', $articleData) || $articleData['imageUrl'] === null) {
            return null;
        }

        return FeedItemImageHelper::limitWidthInImageUrl($articleData['imageUrl']);
    }
}
