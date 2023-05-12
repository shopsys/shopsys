<?php

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class HourlyFeedCronModule implements SimpleCronModuleInterface
{
    protected Logger $logger;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedFacade $feedFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(protected readonly FeedFacade $feedFacade, protected readonly Domain $domain)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function run(): void
    {
        foreach ($this->feedFacade->getFeedsInfo('hourly') as $feedInfo) {
            foreach ($this->domain->getAll() as $domainConfig) {
                $startTime = microtime(true);
                $this->feedFacade->generateFeed($feedInfo->getName(), $domainConfig);
                $endTime = microtime(true);

                $this->logger->debug(sprintf(
                    'Feed "%s" generated on domain "%s" into "%s" in %.3f s',
                    $feedInfo->getName(),
                    $domainConfig->getName(),
                    $this->feedFacade->getFeedFilepath($feedInfo, $domainConfig),
                    $endTime - $startTime
                ));
            }
        }
    }
}
