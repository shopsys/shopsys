<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\PersooBundle\Model\FeedItem;

use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;

class PersooCategoryFeedItem implements FeedItemInterface
{
    /**
     * @param int $id
     * @param string $name
     * @param array $hierarchyIds
     * @param string $url
     * @param array $hierarchyNames
     * @param string|null $description
     * @param string|null $imageUrl
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $name,
        protected readonly array $hierarchyIds,
        protected readonly string $url,
        protected readonly array $hierarchyNames,
        protected readonly ?string $description,
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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->url;
    }

    /**
     * @return string|null
     */
    public function getImageLink(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * @return string
     */
    public function getHierarchyIds(): string
    {
        return implode(':', $this->hierarchyIds);
    }

    /**
     * @return string
     */
    public function getHierarchyText(): string
    {
        return implode(' | ', $this->hierarchyNames);
    }
}
