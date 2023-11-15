<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use DateTimeZone;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Feed\Exception\FeedNameNotUniqueException;
use Shopsys\FrameworkBundle\Model\Feed\Exception\FeedNotFoundException;

class FeedRegistry
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Feed\FeedConfig[]
     */
    protected array $feedConfigsByName = [];

    /**
     * @param string|null $cronTimeZone
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver $cronTimeResolver
     * @param \Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig $cronConfig
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly ?string $cronTimeZone,
        protected readonly CronTimeResolver $cronTimeResolver,
        protected readonly CronConfig $cronConfig,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInterface $feed
     * @param string $timeHours
     * @param string $timeMinutes
     * @param array $domainIds
     */
    public function registerFeed(FeedInterface $feed, string $timeHours, string $timeMinutes, array $domainIds): void
    {
        $this->cronTimeResolver->validateTimeString($timeHours, 23, 1);
        $this->cronTimeResolver->validateTimeString($timeMinutes, 55, 1);

        $name = $feed->getInfo()->getName();
        $this->assertNameIsUnique($name);

        $domainIds = $domainIds === [] ? $this->domain->getAllIds() : $domainIds;

        $this->feedConfigsByName[$name] = new FeedConfig($feed, $timeHours, $timeMinutes, $domainIds);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedConfig[]
     */
    public function getFeedConfigsForCurrentTime(): array
    {
        $timeZone = new DateTimeZone($this->cronTimeZone ?? date_default_timezone_get());
        $matchedFeedConfig = [];

        foreach ($this->feedConfigsByName as $feedConfig) {
            if ($this->cronTimeResolver->isValidAtTime(
                $feedConfig,
                DateTimeHelper::getCurrentRoundedTimeForIntervalAndTimezone(
                    $this->getFeedCronModuleRunEveryMinuteValue(),
                    $timeZone,
                ),
            )) {
                $matchedFeedConfig[] = $feedConfig;
            }
        }

        return $matchedFeedConfig;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedInterface[]
     */
    public function getFeedsForCurrentTime(): array
    {
        return array_map(fn (FeedConfig $feedConfig) => $feedConfig->getFeed(), $this->getFeedConfigsForCurrentTime());
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedConfig[]
     */
    public function getAllFeedConfigs(): array
    {
        return $this->feedConfigsByName;
    }

    /**
     * @param string $name
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedConfig
     */
    public function getFeedConfigByName(string $name): FeedConfig
    {
        if (!array_key_exists($name, $this->feedConfigsByName)) {
            throw new FeedNotFoundException($name);
        }

        return $this->feedConfigsByName[$name];
    }

    /**
     * @param string $name
     */
    protected function assertNameIsUnique(string $name): void
    {
        if (array_key_exists($name, $this->feedConfigsByName)) {
            throw new FeedNameNotUniqueException($name);
        }
    }

    /**
     * @return int
     */
    protected function getFeedCronModuleRunEveryMinuteValue(): int
    {
        $feedCronModule = $this->cronConfig->getCronModuleConfigByServiceId(FeedCronModule::class);

        return $feedCronModule->getRunEveryMin();
    }
}
