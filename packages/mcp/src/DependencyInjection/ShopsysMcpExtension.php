<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\DependencyInjection;

use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ShopsysMcpExtension extends Extension implements PrependExtensionInterface
{
    #[Override]
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Shopsys\McpBundle\Migrations' => __DIR__ . '/../Migrations',
            ],
        ]);
    }

    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('shopsys_mcp.query.statement_timeout_ms', $config['query']['statement_timeout_ms']);
        $container->setParameter('shopsys_mcp.query.lock_timeout_ms', $config['query']['lock_timeout_ms']);
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }
}
