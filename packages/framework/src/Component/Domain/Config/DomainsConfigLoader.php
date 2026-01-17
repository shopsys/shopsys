<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain\Config;

use DateTimeZone;
use Shopsys\FrameworkBundle\Component\Domain\Config\Exception\DomainConfigsDoNotMatchException;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

class DomainsConfigLoader
{
    public function __construct(
        protected readonly Filesystem $filesystem,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function loadDomainConfigsFromYaml(string $domainsConfigFilepath, string $domainsUrlsConfigFilepath): array
    {
        $processedConfig = $this->getProcessedConfig($domainsConfigFilepath, $this->getDomainsConfigDefinition());
        $processedUrlsConfig = $this->getProcessedConfig(
            $domainsUrlsConfigFilepath,
            $this->getDomainsUrlsConfigDefinition(),
        );
        $domainConfigsByDomainId = $processedConfig[DomainsConfigDefinition::CONFIG_DOMAINS];
        $domainUrlsConfigsByDomainId = $processedUrlsConfig[DomainsUrlsConfigDefinition::CONFIG_DOMAINS_URLS];

        if (!$this->isConfigMatchingUrlsConfig($domainConfigsByDomainId, $domainUrlsConfigsByDomainId)) {
            $message =
                'File ' . $domainsUrlsConfigFilepath . ' does not contain urls for all domains listed in ' . $domainsConfigFilepath;

            throw new DomainConfigsDoNotMatchException($message);
        }
        $processedConfigsWithUrlsByDomainId = $this->addUrlsToProcessedConfig(
            $domainConfigsByDomainId,
            $domainUrlsConfigsByDomainId,
        );

        return $this->loadDomainConfigsFromArray($processedConfigsWithUrlsByDomainId);
    }

    protected function getDomainsConfigDefinition(): DomainsConfigDefinition
    {
        return new DomainsConfigDefinition();
    }

    protected function getDomainsUrlsConfigDefinition(): DomainsUrlsConfigDefinition
    {
        return new DomainsUrlsConfigDefinition();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    protected function loadDomainConfigsFromArray(array $processedConfigsByDomainId): array
    {
        $domainConfigs = [];

        foreach ($processedConfigsByDomainId as $domainConfigArray) {
            $domainConfigs[] = $this->processDomainConfigArray($domainConfigArray);
        }

        return $domainConfigs;
    }

    protected function processDomainConfigArray(array $domainConfig): DomainConfig
    {
        $url = $domainConfig[DomainsUrlsConfigDefinition::CONFIG_URL];
        $postfix = parse_url($url, PHP_URL_PATH);
        $baseUrl = $url;

        if ($postfix !== null) {
            $baseUrl = $this->transformStringHelper->removeStringFromEnd($baseUrl, $postfix);
        }

        return new DomainConfig(
            $domainConfig[DomainsConfigDefinition::CONFIG_ID],
            $url,
            $domainConfig[DomainsConfigDefinition::CONFIG_NAME],
            $domainConfig[DomainsConfigDefinition::CONFIG_LOCALE],
            new DateTimeZone($domainConfig[DomainsConfigDefinition::CONFIG_TIMEZONE]),
            $baseUrl,
            $domainConfig[DomainsConfigDefinition::CONFIG_TYPE],
            $domainConfig[DomainsConfigDefinition::CONFIG_LOAD_DEMO_DATA],
            $postfix,
        );
    }

    protected function addUrlsToProcessedConfig(
        array $domainConfigsByDomainId,
        array $domainUrlsConfigsByDomainId,
    ): array {
        foreach ($domainConfigsByDomainId as $domainId => $domainConfigArray) {
            $domainConfigArray[DomainsUrlsConfigDefinition::CONFIG_URL] =
                $domainUrlsConfigsByDomainId[$domainId][DomainsUrlsConfigDefinition::CONFIG_URL];
            $domainConfigsByDomainId[$domainId] = $domainConfigArray;
        }

        return $domainConfigsByDomainId;
    }

    protected function getProcessedConfig(string $filepath, ConfigurationInterface $configDefinition): array
    {
        $yamlParser = new Parser();
        $processor = new Processor();

        if (!$this->filesystem->exists($filepath)) {
            throw new FileNotFoundException(
                'File ' . $filepath . ' does not exist',
            );
        }

        $parsedConfig = $yamlParser->parse(file_get_contents($filepath));

        return $processor->processConfiguration($configDefinition, [$parsedConfig]);
    }

    protected function isConfigMatchingUrlsConfig(
        array $domainConfigsByDomainId,
        array $domainUrlsConfigsByDomainId,
    ): bool {
        foreach (array_keys($domainConfigsByDomainId) as $domainId) {
            if (!array_key_exists($domainId, $domainUrlsConfigsByDomainId)) {
                return false;
            }
        }

        return true;
    }
}
