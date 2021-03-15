<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations;

use PDO;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainsConfigDefinition;
use Shopsys\MigrationBundle\Component\Doctrine\Migrations\AbstractMigration as BaseAbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractMigration extends BaseAbstractMigration implements ContainerAwareInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface|null $container
     */
    public function setContainer(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    protected function getAllDomainIds(): array
    {
        return array_map(function ($domainConfig) {
            return $domainConfig[DomainsConfigDefinition::CONFIG_ID];
        }, $this->getParsedDomainConfigs());
    }

    /**
     * @return array
     */
    protected function getCreatedDomainIds(): array
    {
        return $this->sql('SELECT domain_id FROM setting_values WHERE name = :baseUrl', ['baseUrl' => 'baseUrl'])
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getDomainLocale(int $domainId): string
    {
        $domainConfigByDomainId = array_filter(
            $this->getParsedDomainConfigs(),
            function ($domainConfig) use ($domainId) {
                return $domainConfig[DomainsConfigDefinition::CONFIG_ID] === $domainId;
            }
        );

        return array_shift($domainConfigByDomainId)[DomainsConfigDefinition::CONFIG_LOCALE];
    }

    /**
     * @return array
     */
    protected function getParsedDomainConfigs(): array
    {
        $domainConfigFilePath = $this->container->getParameter('shopsys.domain_config_filepath');
        return Yaml::parseFile($domainConfigFilePath)[DomainsConfigDefinition::CONFIG_DOMAINS];
    }
}
