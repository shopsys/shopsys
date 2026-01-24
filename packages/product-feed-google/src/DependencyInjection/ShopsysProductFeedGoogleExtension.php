<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle\DependencyInjection;

use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ShopsysProductFeedGoogleExtension extends Extension implements PrependExtensionInterface
{
    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

    #[Override]
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Shopsys\ProductFeed\GoogleBundle\Migrations' => __DIR__ . '/../Migrations',
            ],
        ]);
    }
}
