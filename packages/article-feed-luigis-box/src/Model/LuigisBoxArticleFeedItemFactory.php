<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle\Model;

use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;

class LuigisBoxArticleFeedItemFactory
{
    /**
     * @param array $articleData
     * @param int $itemNumber
     * @return \Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItem
     */
    public function create(array $articleData, int $itemNumber): LuigisBoxArticleFeedItem
    {
        return new LuigisBoxArticleFeedItem(
            $itemNumber,
            $articleData['name'],
            $articleData['url'],
            TransformString::convertHtmlToPlainText($articleData['text']),
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

        return ImageUrlWithSizeHelper::limitWidthInImageUrl($articleData['imageUrl']);
    }
}
