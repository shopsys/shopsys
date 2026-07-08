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

    public function __construct(
        protected readonly ?string $cronTimeZone,
        protected readonly CronTimeResolver $cronTimeResolver,
        protected readonly CronConfig $cronConfig,
        protected readonly Domain $domain,
        protected readonly DateTimeHelper $dateTimeHelper,
    ) {
    }

    /**
     * @param int[] $domainIds
     * @param string|array<int, string[]>|null $currenciesConfig
     */
    public function registerFeed(
        FeedInterface $feed,
        string $cronExpression,
        array $domainIds,
        string|array|null $currenciesConfig = null,
    ): void {
        $this->cronTimeResolver->validateCronExpression($cronExpression);

        $name = $feed->getInfo()->getName();
        $this->assertNameIsUnique($name);

        $domainIds = $domainIds === [] ? $this->domain->getAllIds() : $domainIds;

        $this->feedConfigsByName[$name] = new FeedConfig($feed, $cronExpression, $domainIds, $currenciesConfig);
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
                $this->dateTimeHelper->getCurrentRoundedTimeForIntervalAndTimezone(
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

    public function getFeedConfigByName(string $name): FeedConfig
    {
        if (!array_key_exists($name, $this->feedConfigsByName)) {
            throw new FeedNotFoundException($name);
        }

        return $this->feedConfigsByName[$name];
    }

    protected function assertNameIsUnique(string $name): void
    {
        if (array_key_exists($name, $this->feedConfigsByName)) {
            throw new FeedNameNotUniqueException($name);
        }
    }

    protected function getFeedCronModuleRunEveryMinuteValue(): int
    {
        $feedCronModule = $this->cronConfig->getCronModuleConfigByServiceId(FeedCronModule::class);

        return $feedCronModule->getRunEveryMin();
    }
}
