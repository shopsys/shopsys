<?php

declare(strict_types=1);

namespace App;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Security\Filesystem\FilemanagerAccess;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    public function boot(): void
    {
        parent::boot();

        $filemanagerAccess = $this->container->get(FilemanagerAccess::class);
        FilemanagerAccess::injectSelf($filemanagerAccess);

        $translator = $this->container->get('translator');
        Translator::injectSelf($translator);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $container
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $builder
     */
    protected function configureContainer(
        ContainerConfigurator $container,
        LoaderInterface $loader,
        ContainerBuilder $builder,
    ): void {
        $configDir = $this->getConfigDir();

        $container->import($configDir . '/{packages}/*' . self::CONFIG_EXTS);
        $container->import($configDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS);
        $container->import($configDir . '/{services}' . self::CONFIG_EXTS);
        $container->import($configDir . '/{services}_' . $this->environment . self::CONFIG_EXTS);

        if (file_exists(__DIR__ . '/../../../parameters_monorepo.yaml')) {
            $container->import(__DIR__ . '/../../../parameters_monorepo.yaml');
        }

        if (file_exists($configDir . '/parameters_version.yaml')) {
            $container->import($configDir . '/parameters_version.yaml');
        }

        if (file_exists($configDir . '/parameters.yaml')) {
            $container->import($configDir . '/parameters.yaml');
        }
    }

    /**
     * @param \Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator $routes
     */
    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $configDir = $this->getConfigDir();

        $routes->import($configDir . '/{routes}/*' . self::CONFIG_EXTS);
        $routes->import($configDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS);
        $routes->import($configDir . '/{routes}' . self::CONFIG_EXTS);
    }
}
