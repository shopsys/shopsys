<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Override;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeInterface;

class FeedConfig implements CronTimeInterface
{
    /**
     * @param int[] $domainIds
     */
    public function __construct(
        protected readonly FeedInterface $feed,
        protected readonly string $cronExpression,
        protected readonly array $domainIds,
    ) {
    }

    public function getFeed(): FeedInterface
    {
        return $this->feed;
    }

    #[Override]
    public function getCronExpression(): string
    {
        return $this->cronExpression;
    }

    /**
     * @return int[]
     */
    public function getDomainIds(): array
    {
        return $this->domainIds;
    }
}
