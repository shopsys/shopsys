<?php

namespace Shopsys\FrameworkBundle\Component\Domain\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Parser;

class DomainsConfigLoader
{
    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    public function loadDomainConfigsFromYaml(string $domainsConfigFilepath, string $domainsUrlsConfigFilepath): array
    {
        $processedConfig = $this->getProcessedConfig($domainsConfigFilepath, new DomainsConfigDefinition());
        $processedUrlsConfig = $this->getProcessedConfig($domainsUrlsConfigFilepath, new DomainsUrlsConfigDefinition());
        $domainConfigsByDomainId = $processedConfig[DomainsConfigDefinition::CONFIG_DOMAINS];
        $domainUrlsConfigsByDomainId = $processedUrlsConfig[DomainsUrlsConfigDefinition::CONFIG_DOMAINS_URLS];

        if (!$this->isConfigMatchingUrlsConfig($domainConfigsByDomainId, $domainUrlsConfigsByDomainId)) {
            $message =
                'File ' . $domainsUrlsConfigFilepath . ' does not contain urls for all domains listed in ' . $domainsConfigFilepath;
            throw new \Shopsys\FrameworkBundle\Component\Domain\Config\Exception\DomainConfigsDoNotMatchException($message);
        }
        $processedConfigsWithUrlsByDomainId = $this->addUrlsToProcessedConfig($domainConfigsByDomainId, $domainUrlsConfigsByDomainId);

        $domainConfigs = $this->loadDomainConfigsFromArray($processedConfigsWithUrlsByDomainId);

        return $domainConfigs;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[]
     */
    private function loadDomainConfigsFromArray(array $processedConfigsByDomainId): array
    {
        $domainConfigs = [];

        foreach ($processedConfigsByDomainId as $domainConfigArray) {
            $domainConfigs[] = $this->processDomainConfigArray($domainConfigArray);
        }

        return $domainConfigs;
    }

    private function processDomainConfigArray(array $domainConfig): \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig
    {
        return new DomainConfig(
            $domainConfig[DomainsConfigDefinition::CONFIG_ID],
            $domainConfig[DomainsUrlsConfigDefinition::CONFIG_URL],
            $domainConfig[DomainsConfigDefinition::CONFIG_NAME],
            $domainConfig[DomainsConfigDefinition::CONFIG_LOCALE],
            $domainConfig[DomainsConfigDefinition::CONFIG_STYLES_DIRECTORY]
        );
    }
    
    private function addUrlsToProcessedConfig(array $domainConfigsByDomainId, array $domainUrlsConfigsByDomainId): array
    {
        foreach ($domainConfigsByDomainId as $domainId => $domainConfigArray) {
            $domainConfigArray[DomainsUrlsConfigDefinition::CONFIG_URL] =
                $domainUrlsConfigsByDomainId[$domainId][DomainsUrlsConfigDefinition::CONFIG_URL];
            $domainConfigsByDomainId[$domainId] = $domainConfigArray;
        }

        return $domainConfigsByDomainId;
    }
    
    private function getProcessedConfig(string $filepath, ConfigurationInterface $configDefinition): array
    {
        $yamlParser = new Parser();
        $processor = new Processor();

        if (!$this->filesystem->exists($filepath)) {
            throw new \Symfony\Component\Filesystem\Exception\FileNotFoundException(
                'File ' . $filepath . ' does not exist'
            );
        }

        $parsedConfig = $yamlParser->parse(file_get_contents($filepath));

        return $processor->processConfiguration($configDefinition, [$parsedConfig]);
    }
    
    private function isConfigMatchingUrlsConfig(array $domainConfigsByDomainId, array $domainUrlsConfigsByDomainId): bool
    {
        foreach (array_keys($domainConfigsByDomainId) as $domainId) {
            if (!array_key_exists($domainId, $domainUrlsConfigsByDomainId)) {
                return false;
            }
        }

        return true;
    }
}
