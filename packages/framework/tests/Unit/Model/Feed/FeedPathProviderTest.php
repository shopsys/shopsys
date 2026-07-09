<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Feed;

use Override;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedPathProvider;
use Tests\FrameworkBundle\Test\DomainConfigHelper;
use Tests\FrameworkBundle\Unit\TestCase;

class FeedPathProviderTest extends TestCase
{
    private const string FEED_HASH = 'hash123';

    private FeedPathProvider $feedPathProvider;

    private FeedInfoInterface $feedInfo;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $settingStub = $this->createStub(Setting::class);
        $settingStub->method('get')->willReturn(self::FEED_HASH);

        $this->feedPathProvider = new FeedPathProvider('/feeds/', '/content/feeds/', '/project', $settingStub);

        $feedInfoStub = $this->createStub(FeedInfoInterface::class);
        $feedInfoStub->method('getName')->willReturn('mergado');
        $this->feedInfo = $feedInfoStub;
    }

    public function testDomainDefaultCurrencyKeepsTheHistoricalFilename(): void
    {
        $domainConfig = DomainConfigHelper::getDomainConfig(currencyCodes: ['EUR', 'CZK']);

        $this->assertSame(
            DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL . '/feeds/' . self::FEED_HASH . '_mergado_1.xml',
            $this->feedPathProvider->getFeedUrl($this->feedInfo, $domainConfig, 'EUR'),
        );
        $this->assertSame(
            '/content/feeds/' . self::FEED_HASH . '_mergado_1.xml',
            $this->feedPathProvider->getFeedFilepath($this->feedInfo, $domainConfig, 'EUR'),
        );
    }

    public function testWithoutCurrencyCodeTheHistoricalFilenameIsUsed(): void
    {
        $domainConfig = DomainConfigHelper::getDomainConfig(currencyCodes: ['EUR', 'CZK']);

        $this->assertSame(
            '/content/feeds/' . self::FEED_HASH . '_mergado_1.xml',
            $this->feedPathProvider->getFeedFilepath($this->feedInfo, $domainConfig),
        );
    }

    public function testSecondaryCurrencyGetsLowercaseCurrencyCodeSuffix(): void
    {
        $domainConfig = DomainConfigHelper::getDomainConfig(currencyCodes: ['EUR', 'CZK']);

        $this->assertSame(
            DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL . '/feeds/' . self::FEED_HASH . '_mergado_1_czk.xml',
            $this->feedPathProvider->getFeedUrl($this->feedInfo, $domainConfig, 'CZK'),
        );
        $this->assertSame(
            '/content/feeds/' . self::FEED_HASH . '_mergado_1_czk.xml',
            $this->feedPathProvider->getFeedFilepath($this->feedInfo, $domainConfig, 'CZK'),
        );
        $this->assertSame(
            '/project/content/feeds/' . self::FEED_HASH . '_mergado_1_czk.xml',
            $this->feedPathProvider->getFeedLocalFilepath($this->feedInfo, $domainConfig, 'CZK'),
        );
    }
}
