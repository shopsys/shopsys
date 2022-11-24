<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Domain\Config;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigLoader;
use Shopsys\FrameworkBundle\Component\Domain\Config\Exception\DomainConfigsDoNotMatchException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Tests\App\Test\FunctionalTestCase;

class DomainsConfigLoaderTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigLoader
     * @inject
     */
    private DomainsConfigLoader $domainsConfigLoader;

    public function testLoadDomainConfigsFromYaml(): void
    {
        $domainsConfigFilepath = $this->getContainer()->getParameter('shopsys.domain_config_filepath');
        $domainsUrlsConfigFilepath = $this->getContainer()->getParameter('shopsys.domain_urls_config_filepath');
        $domainConfigs = $this->domainsConfigLoader->loadDomainConfigsFromYaml(
            $domainsConfigFilepath,
            $domainsUrlsConfigFilepath
        );

        $this->assertGreaterThan(0, count($domainConfigs));

        foreach ($domainConfigs as $domainConfig) {
            $this->assertInstanceOf(DomainConfig::class, $domainConfig);
        }
    }

    public function testLoadDomainConfigsFromYamlConfigFileNotFound(): void
    {
        $domainsUrlsConfigFilepath = $this->getContainer()->getParameter('shopsys.domain_urls_config_filepath');

        $this->expectException(FileNotFoundException::class);
        $this->domainsConfigLoader->loadDomainConfigsFromYaml('nonexistentFilename', $domainsUrlsConfigFilepath);
    }

    public function testLoadDomainConfigsFromYamlUrlsConfigFileNotFound(): void
    {
        $domainsConfigFilepath = $this->getContainer()->getParameter('shopsys.domain_config_filepath');

        $this->expectException(FileNotFoundException::class);
        $this->domainsConfigLoader->loadDomainConfigsFromYaml($domainsConfigFilepath, 'nonexistentFilename');
    }

    public function testLoadDomainConfigsFromYamlDomainConfigsDoNotMatchException(): void
    {
        $domainsConfigFilepath = __DIR__ . '/test_domains.yaml';
        $domainsUrlsConfigFilepath = __DIR__ . '/test_domains_urls.yaml';

        $this->expectException(DomainConfigsDoNotMatchException::class);

        $this->domainsConfigLoader->loadDomainConfigsFromYaml($domainsConfigFilepath, $domainsUrlsConfigFilepath);
    }
}
