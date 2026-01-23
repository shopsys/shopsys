<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle\Model;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;

class LuigisBoxArticleFeedItem implements FeedItemInterface
{
    public const string UNIQUE_BLOG_ARTICLE_IDENTIFIER_PREFIX = 'blog_article';
    public const string UNIQUE_ARTICLE_IDENTIFIER_PREFIX = 'article';

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
        protected readonly ?string $seoTitle = null,
        protected readonly ?string $seoMetaDescription = null,
        protected readonly ?string $seoH1 = null,
    ) {
    }

    #[Override]
    public function getSeekId(): int
    {
        return $this->id;
    }

    public function getIdentity(): string
    {
        return $this->index . '-' . $this->id;
    }

    public function getName(): string
    {
        return $this->title;
    }

    public function getUrl(): string
    {
        return $this->link;
    }

    public function getText(): ?string
    {
        return $this->description;
    }

    public function getAnnotation(): ?string
    {
        return $this->perex;
    }

    public function getImageLinkS(): ?string
    {
        return $this->imageUrlS;
    }

    public function getImageLinkM(): ?string
    {
        return $this->imageUrlM;
    }

    public function getImageLinkL(): ?string
    {
        return $this->imageUrlL;
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function getSeoMetaDescription(): ?string
    {
        return $this->seoMetaDescription;
    }

    public function getSeoH1(): ?string
    {
        return $this->seoH1;
    }
}
