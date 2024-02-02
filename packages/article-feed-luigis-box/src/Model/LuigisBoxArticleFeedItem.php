<?php

declare(strict_types=1);

namespace Shopsys\ArticleFeed\LuigisBoxBundle\Model;

use Shopsys\FrameworkBundle\Model\Feed\FeedItemInterface;

class LuigisBoxArticleFeedItem implements FeedItemInterface
{
    /**
     * @param int $id
     * @param string $title
     * @param string $link
     * @param string|null $description
     * @param string|null $imageLink
     */
    public function __construct(
        protected readonly int $id,
        public readonly string $title,
        public readonly string $link,
        public readonly ?string $description,
        public readonly ?string $imageLink,
    ) {
    }

    /**
     * @return int
     */
    public function getSeekId(): int
    {
        return $this->id;
    }
}
