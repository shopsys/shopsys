<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Domain\Config;

use DateTimeZone;
use Shopsys\FrameworkBundle\Component\Domain\Config\Exception\DomainConfigsDoNotMatchException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

class DomainsConfigLoader
{
    /**
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     */
    public function __construct(protected readonly Filesystem $filesystem)
    {
    }

    /**
     * @param string $domainsConfigFilepath
     * @param string $domainsUrlsConfigFilepath
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function loadDomainConfigsFromYaml($domainsConfigFilepath, $domainsUrlsConfigFilepath): array
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

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigDefinition
     */
    protected function getDomainsConfigDefinition(): DomainsConfigDefinition
    {
        return new DomainsConfigDefinition();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainsUrlsConfigDefinition
     */
    protected function getDomainsUrlsConfigDefinition(): DomainsUrlsConfigDefinition
    {
        return new DomainsUrlsConfigDefinition();
    }

    /**
     * @param mixed[] $processedConfigsByDomainId
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    protected function loadDomainConfigsFromArray($processedConfigsByDomainId): array
    {
        $domainConfigs = [];

        foreach ($processedConfigsByDomainId as $domainConfigArray) {
            $domainConfigs[] = $this->processDomainConfigArray($domainConfigArray);
        }

        return $domainConfigs;
    }

    /**
     * @param mixed[] $domainConfig
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    protected function processDomainConfigArray(array $domainConfig): \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
    {
        return new DomainConfig(
            $domainConfig[DomainsConfigDefinition::CONFIG_ID],
            $domainConfig[DomainsUrlsConfigDefinition::CONFIG_URL],
            $domainConfig[DomainsConfigDefinition::CONFIG_NAME],
            $domainConfig[DomainsConfigDefinition::CONFIG_LOCALE],
            new DateTimeZone($domainConfig[DomainsConfigDefinition::CONFIG_TIMEZONE]),
            $domainConfig[DomainsConfigDefinition::CONFIG_STYLES_DIRECTORY],
            $domainConfig[DomainsConfigDefinition::CONFIG_DESIGN_ID],
        );
    }

    /**
     * @param mixed[] $domainConfigsByDomainId
     * @param mixed[] $domainUrlsConfigsByDomainId
     * @return mixed[]
     */
    protected function addUrlsToProcessedConfig($domainConfigsByDomainId, $domainUrlsConfigsByDomainId): array
    {
        foreach ($domainConfigsByDomainId as $domainId => $domainConfigArray) {
            $domainConfigArray[DomainsUrlsConfigDefinition::CONFIG_URL] =
                $domainUrlsConfigsByDomainId[$domainId][DomainsUrlsConfigDefinition::CONFIG_URL];
            $domainConfigsByDomainId[$domainId] = $domainConfigArray;
        }

        return $domainConfigsByDomainId;
    }

    /**
     * @param string $filepath
     * @param \Symfony\Component\Config\Definition\ConfigurationInterface $configDefinition
     * @return mixed[]
     */
    protected function getProcessedConfig($filepath, ConfigurationInterface $configDefinition): array
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

    /**
     * @param mixed[] $domainConfigsByDomainId
     * @param mixed[] $domainUrlsConfigsByDomainId
     * @return bool
     */
    protected function isConfigMatchingUrlsConfig($domainConfigsByDomainId, $domainUrlsConfigsByDomainId): bool
    {
        foreach (array_keys($domainConfigsByDomainId) as $domainId) {
            if (!array_key_exists($domainId, $domainUrlsConfigsByDomainId)) {
                return false;
            }
        }

        return true;
    }
}
