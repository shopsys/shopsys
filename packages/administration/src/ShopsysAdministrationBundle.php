<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle;

use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ShopsysAdministrationBundle extends AbstractBundle
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Shopsys\AdministrationBundle\Migrations' => __DIR__ . '/Migrations',
            ],
        ]);

        $thirdPartyBundlesViewFileLocator = (new FileLocator(__DIR__ . '/../templates/bundles'));

        $builder->loadFromExtension('twig', [
            'paths' => [
                $thirdPartyBundlesViewFileLocator->locate('TwigBundle') => 'Twig',
            ],
        ]);
    }
}
