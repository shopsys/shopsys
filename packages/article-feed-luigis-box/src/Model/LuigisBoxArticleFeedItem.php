<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle\Model;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;

class LuigisBoxArticleFeedItem implements FeedItemInterface
{
    public const string UNIQUE_BLOG_ARTICLE_IDENTIFIER_PREFIX = 'blog_article';
    public const string UNIQUE_ARTICLE_IDENTIFIER_PREFIX = 'article';

    /**
     * @param int $id
     * @param string $index
     * @param string $title
     * @param string $link
     * @param string|null $description
     * @param string|null $perex
     * @param string|null $imageUrlS
     * @param string|null $imageUrlM
     * @param string|null $imageUrlL
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $index,
        protected readonly string $title,
        protected readonly string $link,
        protected readonly ?string $description,
        protected readonly ?string $perex,
        protected readonly ?string $imageUrlS = null,
        protected readonly ?string $imageUrlM = null,
        protected readonly ?string $imageUrlL = null,
    ) {
    }

    /**
     * @return int
     */
    #[Override]
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
        return $this->imageUrlS;
    }

    /**
     * @return string|null
     */
    public function getImageLinkM(): ?string
    {
        return $this->imageUrlM;
    }

    /**
     * @return string|null
     */
    public function getImageLinkL(): ?string
    {
        return $this->imageUrlL;
    }
}
