<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle;

use Override;
use Shopsys\MakerBundle\Maker\BaseMaker;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ShopsysMakerBundle extends AbstractBundle
{
    #[Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $builder->registerForAutoconfiguration(BaseMaker::class)
            ->addTag('maker.command');
    }

    #[Override]
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Shopsys\MakerBundle\Migrations' => __DIR__ . '/Migrations',
            ],
        ]);
    }
}
