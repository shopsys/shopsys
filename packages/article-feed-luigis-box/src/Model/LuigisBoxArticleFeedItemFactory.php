<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle\Model;

use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;

class LuigisBoxArticleFeedItemFactory
{
    protected const int SMALL_IMAGE_SIZE = 100;
    protected const int MEDIUM_IMAGE_SIZE = 200;
    protected const int LARGE_IMAGE_SIZE = 600;

    public function __construct(
        protected readonly ImageUrlWithSizeHelper $imageUrlWithSizeHelper,
    ) {
    }

    public function create(array $articleData): LuigisBoxArticleFeedItem
    {
        $imageUrl = $articleData['imageUrl'] ?? null;

        return new LuigisBoxArticleFeedItem(
            $articleData['id'],
            $articleData['index'],
            $articleData['name'],
            $articleData['url'],
            TransformStringHelper::convertHtmlToPlainText($articleData['text']),
            TransformStringHelper::convertHtmlToPlainText($articleData['perex'] ?? null),
            $imageUrl !== null ? $this->imageUrlWithSizeHelper->limitSizeInImageUrl($imageUrl, static::SMALL_IMAGE_SIZE, static::SMALL_IMAGE_SIZE) : null,
            $imageUrl !== null ? $this->imageUrlWithSizeHelper->limitSizeInImageUrl($imageUrl, static::MEDIUM_IMAGE_SIZE, static::MEDIUM_IMAGE_SIZE) : null,
            $imageUrl !== null ? $this->imageUrlWithSizeHelper->limitSizeInImageUrl($imageUrl, static::LARGE_IMAGE_SIZE, static::LARGE_IMAGE_SIZE) : null,
            $articleData['seoTitle'] ?? null,
            $articleData['seoMetaDescription'] ?? null,
            $articleData['seoH1'] ?? null,
        );
    }
}
