<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\DependencyInjection;

use Override;
use Shopsys\AdministrationBundle\Component\Configuration\AccessControlConfiguration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ShopsysAdministrationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // Register AccessControlConfiguration as a service
        $accessControlConfigDefinition = new Definition(AccessControlConfiguration::class);
        $accessControlConfigDefinition->setArguments([
            $config['access_control']['additional_excluded_route_names'],
        ]);
        $container->setDefinition(AccessControlConfiguration::class, $accessControlConfigDefinition);

        // Load services
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAlias(): string
    {
        return Configuration::EXTENSION_ALIAS;
    }
}
