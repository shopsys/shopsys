<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Domain\Config;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigDefinition;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class DomainsConfigDefinitionTest extends TestCase
{
    private function processDomainConfig(array $domainConfig): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(
            new DomainsConfigDefinition(),
            [
                [
                    DomainsConfigDefinition::CONFIG_DOMAINS => [$domainConfig],
                ],
            ],
        );
    }

    /**
     * @param string[] $currencyCodes
     */
    private function getDomainConfigArray(array $currencyCodes): array
    {
        return [
            DomainsConfigDefinition::CONFIG_ID => 1,
            DomainsConfigDefinition::CONFIG_NAME => 'Example',
            DomainsConfigDefinition::CONFIG_LOCALE => 'en',
            DomainsConfigDefinition::CONFIG_TIMEZONE => 'Europe/Prague',
            DomainsConfigDefinition::CONFIG_CURRENCIES => $currencyCodes,
        ];
    }

    public function testCurrenciesArePreservedInConfiguredOrder(): void
    {
        $processedConfig = $this->processDomainConfig($this->getDomainConfigArray(['EUR', 'CZK', 'USD']));

        $this->assertSame(
            ['EUR', 'CZK', 'USD'],
            $processedConfig[DomainsConfigDefinition::CONFIG_DOMAINS][1][DomainsConfigDefinition::CONFIG_CURRENCIES],
        );
    }

    public function testMissingCurrenciesKeyIsInvalid(): void
    {
        $domainConfig = $this->getDomainConfigArray([]);
        unset($domainConfig[DomainsConfigDefinition::CONFIG_CURRENCIES]);

        $this->expectException(InvalidConfigurationException::class);
        $this->processDomainConfig($domainConfig);
    }

    public function testEmptyCurrenciesAreInvalid(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->processDomainConfig($this->getDomainConfigArray([]));
    }

    public function testDuplicateCurrencyCodesAreInvalid(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->processDomainConfig($this->getDomainConfigArray(['CZK', 'CZK']));
    }

    public function testLowercaseCurrencyCodeIsInvalid(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->processDomainConfig($this->getDomainConfigArray(['czk']));
    }

    public function testTooLongCurrencyCodeIsInvalid(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->processDomainConfig($this->getDomainConfigArray(['CZKX']));
    }
}
