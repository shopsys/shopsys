<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle\Model;

use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;

class LuigisBoxArticleFeedItem implements FeedItemInterface
{
    public const UNIQUE_BLOG_ARTICLE_IDENTIFIER_PREFIX = 'blog_article';
    public const UNIQUE_ARTICLE_IDENTIFIER_PREFIX = 'article';
    protected const SMALL_IMAGE_SIZE = 100;
    protected const MEDIUM_IMAGE_SIZE = 200;
    protected const LARGE_IMAGE_SIZE = 600;

    /**
     * @param int $id
     * @param string $index
     * @param string $title
     * @param string $link
     * @param string|null $description
     * @param string|null $perex
     * @param string|null $imageUrl
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $index,
        protected readonly string $title,
        protected readonly string $link,
        protected readonly ?string $description,
        protected readonly ?string $perex,
        protected readonly ?string $imageUrl = null,
    ) {
    }

    /**
     * @return int
     */
    public function getSeekId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getIdentity(): string
    {
        return $this->index . '-' . $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->link;
    }

    /**
     * @return string|null
     */
    public function getText(): ?string
    {
        return $this->description;
    }

    /**
     * @return string|null
     */
    public function getAnnotation(): ?string
    {
        return $this->perex;
    }

    /**
     * @return string|null
     */
    public function getImageLinkS(): ?string
    {
        if ($this->imageUrl === null) {
            return null;
        }

        return ImageUrlWithSizeHelper::limitSizeInImageUrl($this->imageUrl, static::SMALL_IMAGE_SIZE, static::SMALL_IMAGE_SIZE);
    }

    /**
     * @return string|null
     */
    public function getImageLinkM(): ?string
    {
        if ($this->imageUrl === null) {
            return null;
        }

        return ImageUrlWithSizeHelper::limitSizeInImageUrl($this->imageUrl, static::MEDIUM_IMAGE_SIZE, static::MEDIUM_IMAGE_SIZE);
    }

    /**
     * @return string|null
     */
    public function getImageLinkL(): ?string
    {
        if ($this->imageUrl === null) {
            return null;
        }

        return ImageUrlWithSizeHelper::limitSizeInImageUrl($this->imageUrl, static::LARGE_IMAGE_SIZE, static::LARGE_IMAGE_SIZE);
    }
}
