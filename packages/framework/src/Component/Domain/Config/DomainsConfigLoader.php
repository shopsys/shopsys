<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Config;

use Shopsys\FrameworkBundle\Component\Domain\Config\Exception\DomainConfigsDoNotMatchException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

/**
 * @phpstan-type DomainConfigArray array{
 *     id: int,
 *     url: string,
 *     name: string,
 *     locale: string,
 *     styles_directory: string,
 *     design_id: ?string,
 * }
 */
class DomainsConfigLoader
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $domainsConfigFilepath
     * @param string $domainsUrlsConfigFilepath
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function loadDomainConfigsFromYaml(string $domainsConfigFilepath, string $domainsUrlsConfigFilepath): array
    {
        $processedConfig = $this->getProcessedConfig($domainsConfigFilepath, $this->getDomainsConfigDefinition());
        $processedUrlsConfig = $this->getProcessedConfig(
            $domainsUrlsConfigFilepath,
            $this->getDomainsUrlsConfigDefinition()
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
            $domainUrlsConfigsByDomainId
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
     * @param DomainConfigArray[] $processedConfigsByDomainId
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

    /**
     * @param DomainConfigArray $domainConfig
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
     */
    protected function processDomainConfigArray(array $domainConfig): DomainConfig
    {
        return new DomainConfig(
            $domainConfig[DomainsConfigDefinition::CONFIG_ID],
            $domainConfig[DomainsUrlsConfigDefinition::CONFIG_URL],
            $domainConfig[DomainsConfigDefinition::CONFIG_NAME],
            $domainConfig[DomainsConfigDefinition::CONFIG_LOCALE],
            $domainConfig[DomainsConfigDefinition::CONFIG_STYLES_DIRECTORY],
            $domainConfig[DomainsConfigDefinition::CONFIG_DESIGN_ID]
        );
    }

    /**
     * @param array<int, DomainConfigArray> $domainConfigsByDomainId
     * @param array<int, array{url: string}> $domainUrlsConfigsByDomainId
     * @return array<int, DomainConfigArray>
     */
    protected function addUrlsToProcessedConfig(array $domainConfigsByDomainId, array $domainUrlsConfigsByDomainId): array
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
    protected function getProcessedConfig(string $filepath, ConfigurationInterface $configDefinition): array
    {
        $yamlParser = new Parser();
        $processor = new Processor();

        if (!$this->filesystem->exists($filepath)) {
            throw new FileNotFoundException(
                'File ' . $filepath . ' does not exist'
            );
        }

        $parsedConfig = $yamlParser->parse(file_get_contents($filepath));

        return $processor->processConfiguration($configDefinition, [$parsedConfig]);
    }

    /**
     * @param array<int, DomainConfigArray> $domainConfigsByDomainId
     * @param array<int, array{url: string}> $domainUrlsConfigsByDomainId
     * @return bool
     */
    protected function isConfigMatchingUrlsConfig(array $domainConfigsByDomainId, array $domainUrlsConfigsByDomainId): bool
    {
        foreach (array_keys($domainConfigsByDomainId) as $domainId) {
            if (!array_key_exists($domainId, $domainUrlsConfigsByDomainId)) {
                return false;
            }
        }

        return true;
    }
}
