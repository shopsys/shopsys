<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Feed;

use Override;
use Shopsys\FrameworkBundle\Model\Feed\Exception\FeedCurrencyNotEnabledOnDomainException;
use Shopsys\FrameworkBundle\Model\Feed\FeedConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedCurrencyResolver;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;
use Tests\FrameworkBundle\Test\DomainConfigHelper;
use Tests\FrameworkBundle\Unit\TestCase;

class FeedCurrencyResolverTest extends TestCase
{
    private FeedCurrencyResolver $feedCurrencyResolver;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->feedCurrencyResolver = new FeedCurrencyResolver();
    }

    public function testWithoutConfigurationOnlyTheDomainDefaultCurrencyIsResolved(): void
    {
        $domainConfig = DomainConfigHelper::getDomainConfig(currencyCodes: ['EUR', 'CZK']);

        $currencyCodes = $this->feedCurrencyResolver->resolveCurrencyCodes($this->createFeedConfig(null), $domainConfig);

        $this->assertSame(['EUR'], $currencyCodes);
    }

    public function testAllConfigurationResolvesEveryDomainCurrencyWithDefaultFirst(): void
    {
        $domainConfig = DomainConfigHelper::getDomainConfig(currencyCodes: ['EUR', 'CZK', 'GBP']);

        $currencyCodes = $this->feedCurrencyResolver->resolveCurrencyCodes($this->createFeedConfig('all'), $domainConfig);

        $this->assertSame(['EUR', 'CZK', 'GBP'], $currencyCodes);
    }

    public function testCommaSeparatedListIsIntersectedWithDomainCurrencies(): void
    {
        $domainConfig = DomainConfigHelper::getDomainConfig(currencyCodes: ['EUR', 'CZK']);

        $currencyCodes = $this->feedCurrencyResolver->resolveCurrencyCodes($this->createFeedConfig('CZK, GBP'), $domainConfig);

        $this->assertSame(['CZK'], $currencyCodes);
    }

    public function testCommaSeparatedListSortsDomainDefaultCurrencyFirst(): void
    {
        $domainConfig = DomainConfigHelper::getDomainConfig(currencyCodes: ['EUR', 'CZK']);

        $currencyCodes = $this->feedCurrencyResolver->resolveCurrencyCodes($this->createFeedConfig('CZK,EUR'), $domainConfig);

        $this->assertSame(['EUR', 'CZK'], $currencyCodes);
    }

    public function testPerDomainConfigurationResolvesListedCurrenciesOnListedDomain(): void
    {
        $domainConfig = DomainConfigHelper::getDomainConfig(currencyCodes: ['EUR', 'CZK']);

        $currencyCodes = $this->feedCurrencyResolver->resolveCurrencyCodes(
            $this->createFeedConfig([1 => ['CZK', 'EUR']]),
            $domainConfig,
        );

        $this->assertSame(['EUR', 'CZK'], $currencyCodes);
    }

    public function testPerDomainConfigurationFallsBackToDefaultCurrencyOnUnlistedDomain(): void
    {
        $domainConfig = DomainConfigHelper::getDomainConfig(id: 2, currencyCodes: ['CZK']);

        $currencyCodes = $this->feedCurrencyResolver->resolveCurrencyCodes(
            $this->createFeedConfig([1 => ['EUR']]),
            $domainConfig,
        );

        $this->assertSame(['CZK'], $currencyCodes);
    }

    public function testPerDomainConfigurationWithCurrencyNotEnabledOnDomainThrowsException(): void
    {
        $domainConfig = DomainConfigHelper::getDomainConfig(currencyCodes: ['EUR']);

        $this->expectException(FeedCurrencyNotEnabledOnDomainException::class);

        $this->feedCurrencyResolver->resolveCurrencyCodes($this->createFeedConfig([1 => ['CZK']]), $domainConfig);
    }

    /**
     * @param string|array<int, string[]>|null $currenciesConfig
     */
    private function createFeedConfig(string|array|null $currenciesConfig): FeedConfig
    {
        $feedInfoStub = $this->createStub(FeedInfoInterface::class);
        $feedInfoStub->method('getName')->willReturn('feed_name');

        $feedStub = $this->createStub(FeedInterface::class);
        $feedStub->method('getInfo')->willReturn($feedInfoStub);

        return new FeedConfig($feedStub, '0 0 * * *', [1, 2], $currenciesConfig);
    }
}
