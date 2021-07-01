<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Router\Exception\LocalizedRoutingConfigFileNotFoundException;
use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RequestContext;

class LocalizedRouterFactory
{
    use SetterInjectionTrait;

    /**
     * @deprecated This loader is deprecated and will be removed in the next major. Use Symfony\Bundle\FrameworkBundle\Routing\Router instead of Symfony\Component\Routing\Router without this dependency.
     * @var \Symfony\Component\Config\Loader\LoaderInterface
     */
    protected $configLoader;

    /**
     * @var string
     */
    protected $localeRoutersResourcesFilepathMask;

    /**
     * @var \Symfony\Component\Routing\Router[][]
     */
    protected $routersByLocaleAndHost;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    protected ?ContainerInterface $container;

    /**
     * @var string|null
     */
    protected ?string $cacheDir;

    /**
     * @param string $localeRoutersResourcesFilepathMask
     * @param \Symfony\Component\Config\Loader\LoaderInterface|null $configLoader
     * @param \Symfony\Component\DependencyInjection\ContainerInterface|null $container
     * @param string|null $cacheDir
     */
    public function __construct(
        $localeRoutersResourcesFilepathMask,
        ?LoaderInterface $configLoader = null,
        ?ContainerInterface $container = null,
        ?string $cacheDir = null
    ) {
        $this->configLoader = $configLoader;
        $this->localeRoutersResourcesFilepathMask = $localeRoutersResourcesFilepathMask;
        $this->routersByLocaleAndHost = [];
        $this->container = $container;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @required
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->setDependency($container, 'container');
    }

    /**
     * @param string $locale
     * @param \Symfony\Component\Routing\RequestContext $context
     * @return \Symfony\Component\Routing\Router
     */
    public function getRouter($locale, RequestContext $context)
    {
        if (file_exists($this->getLocaleRouterResourceByLocale($locale)) === false) {
            $message = 'File with localized routes for locale `' . $locale . '` was not found. '
                . 'Please create `' . $this->getLocaleRouterResourceByLocale($locale) . '` file.';
            throw new LocalizedRoutingConfigFileNotFoundException($message);
        }

        if (!array_key_exists($locale, $this->routersByLocaleAndHost)
            || !array_key_exists($context->getHost(), $this->routersByLocaleAndHost[$locale])
        ) {
            $this->routersByLocaleAndHost[$locale][$context->getHost()] = new Router(
                $this->container,
                $this->getLocaleRouterResourceByLocale($locale),
                $this->getRouterOptions($locale),
                $context
            );
        }

        return $this->routersByLocaleAndHost[$locale][$context->getHost()];
    }

    /**
     * @param string $locale
     * @return string
     */
    protected function getLocaleRouterResourceByLocale(string $locale): string
    {
        return str_replace('*', $locale, $this->localeRoutersResourcesFilepathMask);
    }

    /**
     * @param string $locale
     * @return string
     */
    protected function getRoutingCacheDir(string $locale): string
    {
        if ($this->cacheDir === null) {
            DeprecationHelper::trigger(
                'The argument "$cacheDir" is not provided by constructor in "%s". In the next major it will be required.',
                self::class
            );

            $this->cacheDir = $this->container->getParameter('shopsys.router.localized.cache_dir');
        }

        return $this->cacheDir . '/' . $locale;
    }

    /**
     * @param string $locale
     * @return array
     */
    protected function getRouterOptions(string $locale): array
    {
        $options = [];

        if ($this->container->getParameter('kernel.environment') !== EnvironmentType::DEVELOPMENT) {
            $options['cache_dir'] = $this->getRoutingCacheDir($locale);
        }

        return $options;
    }
}
