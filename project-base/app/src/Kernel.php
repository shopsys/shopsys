<?php

declare(strict_types=1);

namespace App;

use Closure;
use Override;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionObject;
use Shopsys\FrameworkBundle\Component\AttributeRouteControllerLoader;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Security\Filesystem\FilemanagerAccess;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\RouteCollection;
use function dirname;
use function is_array;
use const E_DEPRECATED;
use const E_USER_DEPRECATED;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private const string CONFIG_EXTS = '.{php,xml,yaml,yml}';

    #[Override]
    public function boot(): void
    {
        // Disable deprecation notices @see: https://github.com/symfony/symfony-docs/issues/17592
        ErrorHandler::register(null, false)->setLoggers([
            E_DEPRECATED => [null],
            E_USER_DEPRECATED => [null],
        ]);

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
        $isMonorepo = false;

        if (file_exists(__DIR__ . '/../../../parameters_monorepo.yaml')) {
            $isMonorepo = true;
        }

        $container->parameters()->set('shopsys.is_monorepo', $isMonorepo);

        if ($isMonorepo) {
            $frameworkRootDir = $builder->getParameter('kernel.project_dir') . '/../../packages/framework/';
        } else {
            $frameworkRootDir = $builder->getParameter('kernel.project_dir') . '/vendor/shopsys/framework/';
        }


        $container->import($frameworkRootDir . 'src/Resources/config/packages_registry.yaml');

        $container->import($configDir . '/{packages}/*' . self::CONFIG_EXTS);
        $container->import($configDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS);
        $container->import($configDir . '/{services}' . self::CONFIG_EXTS);
        $container->import($configDir . '/{services}_' . $this->environment . self::CONFIG_EXTS);

        if ($isMonorepo) {
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

    /**
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     * @return \Symfony\Component\Routing\RouteCollection
     */
    public function loadRoutes(LoaderInterface $loader): RouteCollection
    {
        $file = (new ReflectionObject($this))->getFileName();
        /** @var \Symfony\Component\Routing\Loader\PhpFileLoader $kernelLoader */
        $kernelLoader = $loader->getResolver()->resolve($file, 'php');
        $kernelLoader->setCurrentDir(dirname($file));
        $collection = new RouteCollection();

        $configureRoutes = new ReflectionMethod($this, 'configureRoutes');
        $configureRoutes->getClosure($this)(new RoutingConfigurator($collection, $kernelLoader, $file, $file, $this->getEnvironment()));

        foreach ($collection as $routeName => $route) {
            $controller = $route->getDefault('_controller');

            if (is_array($controller) && [0, 1] === array_keys($controller) && $this === $controller[0]) {
                $route->setDefault('_controller', ['kernel', $controller[1]]);
            } elseif ($controller instanceof Closure && $this === ($r = new ReflectionFunction($controller))->getClosureThis() && !str_contains($r->name, '{closure')) {
                $route->setDefault('_controller', ['kernel', $r->name]);
            }

            $newRouteName = AttributeRouteControllerLoader::replacePartOfTheRouteName($routeName);

            if ($newRouteName === $routeName) {
                continue;
            }

            $collection->add($newRouteName, $route);
            $collection->remove($routeName);
        }

        return $collection;
    }
}
