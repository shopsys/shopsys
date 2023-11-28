<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Cron\CronTimeInterface;

class FeedConfig implements CronTimeInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInterface $feed
     * @param string $hours
     * @param string $minutes
     * @param int[] $domainIds
     */
    public function __construct(
        protected readonly FeedInterface $feed,
        protected readonly string $hours,
        protected readonly string $minutes,
        protected readonly array $domainIds,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedInterface
     */
    public function getFeed(): FeedInterface
    {
        return $this->feed;
    }

    /**
     * @return string
     */
    public function getTimeMinutes(): string
    {
        return $this->minutes;
    }

    /**
     * @return string
     */
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
