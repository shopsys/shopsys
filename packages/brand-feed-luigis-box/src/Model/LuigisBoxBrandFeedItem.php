<?php

declare(strict_types=1);

namespace Shopsys\BrandFeed\LuigisBoxBundle\Model;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;

class LuigisBoxBrandFeedItem implements FeedItemInterface
{
    public const UNIQUE_BRAND_IDENTIFIER_PREFIX = 'brand';

    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly string $url,
        protected readonly ?string $imageUrl,
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
        return static::UNIQUE_BRAND_IDENTIFIER_PREFIX . '-' . $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
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
