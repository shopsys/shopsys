<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Functional\Component\Domain\Config;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigLoader;
use Tests\ShopBundle\Test\FunctionalTestCase;

class DomainsConfigLoaderTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigLoader $domainsConfigLoader
     * @inject
     */
    private $domainsConfigLoader;

    public function testLoadDomainConfigsFromYaml()
    {
        $domainsConfigFilepath = $this->getContainer()->getParameter('shopsys.domain_config_filepath');
        $domainsUrlsConfigFilepath = $this->getContainer()->getParameter('shopsys.domain_urls_config_filepath');
        $domainConfigs = $this->domainsConfigLoader->loadDomainConfigsFromYaml($domainsConfigFilepath, $domainsUrlsConfigFilepath);

        $this->assertGreaterThan(0, count($domainConfigs));

        foreach ($domainConfigs as $domainConfig) {
            $this->assertInstanceOf(DomainConfig::class, $domainConfig);
        }
    }

    public function testLoadDomainConfigsFromYamlConfigFileNotFound()
    {
        $domainsUrlsConfigFilepath = $this->getContainer()->getParameter('shopsys.domain_urls_config_filepath');

        $this->expectException(\Symfony\Component\Filesystem\Exception\FileNotFoundException::class);
        $this->domainsConfigLoader->loadDomainConfigsFromYaml('nonexistentFilename', $domainsUrlsConfigFilepath);
    }

    public function testLoadDomainConfigsFromYamlUrlsConfigFileNotFound()
    {
        $domainsConfigFilepath = $this->getContainer()->getParameter('shopsys.domain_config_filepath');

        $this->expectException(\Symfony\Component\Filesystem\Exception\FileNotFoundException::class);
        $this->domainsConfigLoader->loadDomainConfigsFromYaml($domainsConfigFilepath, 'nonexistentFilename');
    }

    public function testLoadDomainConfigsFromYamlDomainConfigsDoNotMatchException()
    {
        $domainsConfigFilepath = __DIR__ . '/test_domains.yml';
        $domainsUrlsConfigFilepath = __DIR__ . '/test_domains_urls.yml';

        $this->expectException(\Shopsys\FrameworkBundle\Component\Domain\Config\Exception\DomainConfigsDoNotMatchException::class);

        $this->domainsConfigLoader->loadDomainConfigsFromYaml($domainsConfigFilepath, $domainsUrlsConfigFilepath);
    }
}
