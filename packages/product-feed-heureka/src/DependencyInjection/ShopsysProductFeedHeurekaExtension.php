<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\DependencyInjection;

use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ShopsysProductFeedHeurekaExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('paths.yaml');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Shopsys\ProductFeed\HeurekaBundle\Migrations' => __DIR__ . '/../Migrations',
            ],
        ]);
    }
}
