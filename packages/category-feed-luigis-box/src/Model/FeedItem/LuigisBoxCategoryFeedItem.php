<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;

class LuigisBoxCategoryFeedItem implements FeedItemInterface
{
    public const UNIQUE_IDENTIFIER_PREFIX = 'category-';

    /**
     * @param int $id
     * @param string $name
     * @param string $url
     * @param array $hierarchyNames
     * @param string|null $imageUrl
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly string $url,
        protected readonly array $hierarchyNames,
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
        return static::UNIQUE_IDENTIFIER_PREFIX . $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * @return string|null
     */
    public function getHierarchy(): ?string
    {
        if (count($this->hierarchyNames) > 0) {
            return implode(' | ', $this->hierarchyNames);
        }

        return null;
    }
}
