<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle\Model;

use Shopsys\FrameworkBundle\Component\String\TransformString;

class LuigisBoxArticleFeedItemFactory
{
    /**
     * @param array $articleData
     * @return \Shopsys\ArticleFeed\LuigisBoxBundle\Model\LuigisBoxArticleFeedItem
     */
    public function create(array $articleData): LuigisBoxArticleFeedItem
    {
        return new LuigisBoxArticleFeedItem(
            $articleData['id'],
            $articleData['index'],
            $articleData['name'],
            $articleData['url'],
            TransformString::convertHtmlToPlainText($articleData['text']),
            TransformString::convertHtmlToPlainText($articleData['perex'] ?? null),
            $articleData['imageUrl'] ?? null,
        );
    }
}
