<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle;

use Shopsys\AdministrationBundle\DependencyInjection\Compiler\InicializeControllersCompilerPass;
use Shopsys\AdministrationBundle\DependencyInjection\Compiler\LoadControllersExtensionCompilerPass;
use Shopsys\AdministrationBundle\DependencyInjection\Compiler\RegisterControllerExtensionsCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ShopsysAdministrationBundle extends AbstractBundle
{
    /**
     * {@inheritdoc}
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new InicializeControllersCompilerPass());
        $container->addCompilerPass(new RegisterControllerExtensionsCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 150);
        $container->addCompilerPass(new LoadControllersExtensionCompilerPass());
    }

    /**
     * {@inheritdoc}
     */
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('doctrine_migrations', [
            'migrations_paths' => [
                'Shopsys\AdministrationBundle\Migrations' => __DIR__ . '/Migrations',
            ],
        ]);
    }
}
