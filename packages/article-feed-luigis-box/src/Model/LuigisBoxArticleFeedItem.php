<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle\Model;

use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;

class LuigisBoxArticleFeedItem implements FeedItemInterface
{
    /**
     * @param int $id
     * @param string $index
     * @param string $title
     * @param string $link
     * @param string|null $description
     * @param string|null $perex
     */
    public function __construct(
        protected readonly int $id,
        protected readonly string $index,
        protected readonly string $title,
        protected readonly string $link,
        protected readonly ?string $description,
        protected readonly ?string $perex,
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
}
