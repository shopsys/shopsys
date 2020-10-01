<?php

declare(strict_types=1);

namespace App;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Security\Filesystem\FilemanagerAccess;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;
use function dirname;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;
    private const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    /**
     * @return string
     */
    public function getLogDir(): string
    {
        return $this->getProjectDir() . '/var/log';
    }

    /**
     * @return string
     */
    public function getProjectDir(): string
    {
        return dirname(__DIR__);
    }

    public function boot()
    {
        parent::boot();

        $filemanagerAccess = $this->container->get(FilemanagerAccess::class);
        FilemanagerAccess::injectSelf($filemanagerAccess);

        $translator = $this->container->get('translator');
        Translator::injectSelf($translator);
    }

    /**
     * @return iterable
     */
    public function registerBundles(): iterable
    {
        $contents = require $this->getProjectDir() . '/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir() . '/config/bundles.php'));
        $confDir = $this->getProjectDir() . '/config';

        $loader->load($confDir . '/{packages}/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{packages}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}' . self::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/{services}_' . $this->environment . self::CONFIG_EXTS, 'glob');

        if (file_exists(__DIR__ . '/../../parameters_monorepo.yaml')) {
            $loader->load(__DIR__ . '/../../parameters_monorepo.yaml');
        }

        if (file_exists($confDir . '/parameters_version.yaml')) {
            $loader->load($confDir . '/parameters_version.yaml');
        }

        $this->configureSwiftMailer($container);
    }

    /**
     * @param \Symfony\Component\Routing\RouteCollectionBuilder $routes
     * @throws \Symfony\Component\Config\Exception\FileLoaderLoadException
     */
    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir() . '/config';

        $routes->import($confDir . '/{routes}/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}/' . $this->environment . '/**/*' . self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir . '/{routes}' . self::CONFIG_EXTS, '/', 'glob');
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function configureSwiftMailer(ContainerBuilder $container): void
    {
        $envMailerDeliveryWhitelist = getenv('MAILER_DELIVERY_WHITELIST');
        if ($envMailerDeliveryWhitelist !== false) {
            $mailerDeliveryWhitelist = explode(',', $envMailerDeliveryWhitelist);
            $container->setParameter('mailer_delivery_whitelist', $mailerDeliveryWhitelist);
        }

        $envMailerDisableDelivery = getenv('MAILER_DISABLE_DELIVERY');
        if ($envMailerDisableDelivery !== false) {
            $castedEnvMailerDisableDelivery = (bool)(filter_var(
                $envMailerDisableDelivery,
                FILTER_VALIDATE_BOOLEAN
            ) ?: filter_var(
                $envMailerDisableDelivery,
                FILTER_VALIDATE_INT
            ) ?: filter_var(
                $envMailerDisableDelivery,
                FILTER_VALIDATE_FLOAT
            ));
            $container->setParameter('mailer_disable_delivery', $castedEnvMailerDisableDelivery);
        }

        $envMailerMasterEmailAddress = getenv('MAILER_MASTER_EMAIL_ADDRESS');
        if ($envMailerMasterEmailAddress !== false) {
            $container->setParameter('mailer_master_email_address', $envMailerMasterEmailAddress);
        }
    }
}
