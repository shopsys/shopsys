<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;

class LuigisBoxCategoryFeedItem implements FeedItemInterface
{
    public const UNIQUE_IDENTIFIER_PREFIX = 'category';

    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly string $url,
        protected readonly array $hierarchyNames,
        protected readonly ?string $imageUrl = null,
    ) {
    }

    #[Override]
    public function getSeekId(): int
    {
        return $this->id;
    }

    public function getIdentity(): string
    {
        return static::UNIQUE_IDENTIFIER_PREFIX . '-' . $this->id;
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

    public function getHierarchy(): ?string
    {
        if (count($this->hierarchyNames) > 0) {
            return implode(' | ', $this->hierarchyNames);
        }

        return null;
    }
}
