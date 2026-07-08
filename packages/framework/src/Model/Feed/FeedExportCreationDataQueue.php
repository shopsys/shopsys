<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\Exception\FeedNotFoundException;

/**
 * This class holds the data needed to create an instance of FeedExport using FeedFacade::createFeedExport().
 * Usable for easier iterating over all feeds that need to be created.
 * Every feed module expands into one entry per currency the feed is configured to be generated in.
 *
 * @see \Shopsys\FrameworkBundle\Model\Feed\FeedExport
 * @see \Shopsys\FrameworkBundle\Model\Feed\FeedFacade::createFeedExport()
 */
class FeedExportCreationDataQueue
{
    /**
     * @var array<int, array{feedModule: \Shopsys\FrameworkBundle\Model\Feed\FeedModule, currencyCode: string}>
     */
    protected array $entries = [];

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedModule[] $feedModules
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domains
     */
    public function __construct(
        protected array $feedModules,
        protected array $domains,
        FeedRegistry $feedRegistry,
        FeedCurrencyResolver $feedCurrencyResolver,
    ) {
        foreach ($feedModules as $feedModule) {
            $domainConfig = $domains[$feedModule->getDomainId()];

            try {
                $currencyCodes = $feedCurrencyResolver->resolveCurrencyCodes(
                    $feedRegistry->getFeedConfigByName($feedModule->getName()),
                    $domainConfig,
                );
            } catch (FeedNotFoundException) {
                // the missing feed config is handled (and the feed module deleted) while creating the feed export
                $currencyCodes = [$domainConfig->getDefaultCurrencyCode()];
            }

            foreach ($currencyCodes as $currencyCode) {
                $this->entries[] = [
                    'feedModule' => $feedModule,
                    'currencyCode' => $currencyCode,
                ];
            }
        }
    }

    public function getCurrentFeedName(): string
    {
        return $this->getCurrentFeedModule()->getName();
    }

    public function getCurrentDomain(): DomainConfig
    {
        return $this->domains[$this->getCurrentFeedModule()->getDomainId()];
    }

    public function getCurrentFeedModule(): FeedModule
    {
        return current($this->entries)['feedModule'];
    }

    public function getCurrentCurrencyCode(): string
    {
        return current($this->entries)['currencyCode'];
    }

    /**
     * The feed module may be unscheduled only after its last currency is generated,
     * otherwise a sleep and wake up in the middle of the run would silently skip the remaining currencies
     */
    public function isCurrentLastCurrencyOfFeedModule(): bool
    {
        $nextEntry = $this->entries[key($this->entries) + 1] ?? null;

        return $nextEntry === null || $nextEntry['feedModule'] !== $this->getCurrentFeedModule();
    }

    public function next(): bool
    {
        array_shift($this->entries);

        return !$this->isEmpty();
    }

    public function isEmpty(): bool
    {
        return count($this->entries) === 0;
    }
}
