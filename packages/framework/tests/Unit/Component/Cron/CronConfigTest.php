<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\CronModuleConfig;
use Shopsys\FrameworkBundle\Component\Cron\Config\Exception\SentryMonitoringNotEnabledException;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class CronConfigTest extends TestCase
{
    public function testSentryMonitorConfigIsCreatedWithGivenOptions(): void
    {
        $cronConfig = $this->createCronConfig();
        $cronConfig->registerCronModuleInstance(
            $this->createStub(SimpleCronModuleInterface::class),
            'App\Cron\FooCronModule',
            '* * * * *',
            CronModuleConfig::DEFAULT_INSTANCE_NAME,
            sentryMonitoring: true,
            sentryMaxRuntime: 5,
            sentryCheckinMargin: 10,
            sentryFailureThreshold: 3,
            sentryRecoveryThreshold: 2,
        );

        $sentryMonitorConfig = $cronConfig->getCronModuleConfigByServiceId('App\Cron\FooCronModule')
            ->getSentryMonitorConfig();

        $this->assertNotNull($sentryMonitorConfig);
        $this->assertSame(5, $sentryMonitorConfig->getMaxRuntime());
        $this->assertSame(10, $sentryMonitorConfig->getCheckinMargin());
        $this->assertSame(3, $sentryMonitorConfig->getFailureThreshold());
        $this->assertSame(2, $sentryMonitorConfig->getRecoveryThreshold());
    }

    public function testSentryMonitorConfigIsNullWhenMonitoringIsNotEnabled(): void
    {
        $cronConfig = $this->createCronConfig();
        $cronConfig->registerCronModuleInstance(
            $this->createStub(SimpleCronModuleInterface::class),
            'App\Cron\FooCronModule',
            '* * * * *',
            CronModuleConfig::DEFAULT_INSTANCE_NAME,
        );

        $this->assertNull(
            $cronConfig->getCronModuleConfigByServiceId('App\Cron\FooCronModule')->getSentryMonitorConfig(),
        );
    }

    public function testSentryOptionsWithoutEnabledMonitoringThrowException(): void
    {
        $cronConfig = $this->createCronConfig();

        $this->expectException(SentryMonitoringNotEnabledException::class);

        $cronConfig->registerCronModuleInstance(
            $this->createStub(SimpleCronModuleInterface::class),
            'App\Cron\FooCronModule',
            '* * * * *',
            CronModuleConfig::DEFAULT_INSTANCE_NAME,
            sentryMaxRuntime: 5,
        );
    }

    public function testSlugStripsCronModuleSuffixAndLowercasesClassName(): void
    {
        $slug = $this->registerAndGetSlug('Shopsys\FrameworkBundle\Model\Sitemap\SitemapCronModule');

        $this->assertMatchesRegularExpression('/^sitemap-[0-9a-f]{6}$/', $slug);
    }

    public function testSlugLowercasesMultiWordClassNameIntoSingleToken(): void
    {
        $slug = $this->registerAndGetSlug('App\Model\Product\Elasticsearch\ProductRecalculationCronModule');

        $this->assertMatchesRegularExpression('/^productrecalculation-[0-9a-f]{6}$/', $slug);
    }

    public function testSlugIsDeterministicForSameServiceId(): void
    {
        $this->assertSame(
            $this->registerAndGetSlug('App\Cron\FooCronModule'),
            $this->registerAndGetSlug('App\Cron\FooCronModule'),
        );
    }

    public function testSlugDiffersForDifferentServiceIdsWithSameClassName(): void
    {
        $this->assertNotSame(
            $this->registerAndGetSlug('App\Cron\FooCronModule'),
            $this->registerAndGetSlug('App\Other\FooCronModule'),
        );
    }

    public function testSlugNeverExceedsMaxLength(): void
    {
        $slug = $this->registerAndGetSlug(
            'App\Model\Stock\SynchronizeAvailabilityAndStockQuantitiesFromExternalSystemCronModule',
        );

        $this->assertLessThanOrEqual(50, strlen($slug));
        $this->assertMatchesRegularExpression('/-[0-9a-f]{6}$/', $slug);
    }

    public function testSlugContainsOnlyAllowedCharactersForNonClassNameServiceId(): void
    {
        $slug = $this->registerAndGetSlug('app.cron.foo_module');

        $this->assertMatchesRegularExpression('/^app-cron-foo_module-[0-9a-f]{6}$/', $slug);
    }

    public function testSlugHasNoDoubleDashWhenTruncationLandsOnSeparator(): void
    {
        $slug = $this->registerAndGetSlug(str_repeat('a', 42) . '.tail');

        $this->assertStringNotContainsString('--', $slug);
        $this->assertMatchesRegularExpression('/^a{42}-[0-9a-f]{6}$/', $slug);
    }

    private function registerAndGetSlug(string $serviceId): string
    {
        $cronConfig = $this->createCronConfig();
        $cronConfig->registerCronModuleInstance(
            $this->createStub(SimpleCronModuleInterface::class),
            $serviceId,
            '* * * * *',
            CronModuleConfig::DEFAULT_INSTANCE_NAME,
            sentryMonitoring: true,
        );

        $sentryMonitorConfig = $cronConfig->getCronModuleConfigByServiceId($serviceId)->getSentryMonitorConfig();
        $this->assertNotNull($sentryMonitorConfig);

        return $sentryMonitorConfig->getSlug();
    }

    private function createCronConfig(): CronConfig
    {
        return new CronConfig(new CronTimeResolver(), new TransformStringHelper());
    }
}
