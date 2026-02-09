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
        protected readonly string $hours,
        protected readonly string $minutes,
        protected readonly array $domainIds,
    ) {
    }

    public function getFeed(): FeedInterface
    {
        return $this->feed;
    }

    #[Override]
    public function getTimeMinutes(): string
    {
        return $this->minutes;
    }

    #[Override]
    public function getTimeHours(): string
    {
        return $this->hours;
    }

    /**
     * @return int[]
     */
    public function getDomainIds(): array
    {
        return $this->domainIds;
    }
}
