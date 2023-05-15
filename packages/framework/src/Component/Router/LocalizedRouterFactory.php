<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Router\Exception\LocalizedRoutingConfigFileNotFoundException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RequestContext;

class LocalizedRouterFactory
{
    protected string $localeRoutersResourcesFilepathMask;

    /**
     * @var \Symfony\Component\Routing\Router[][]
     */
    protected array $routersByLocaleAndHost;

    /**
     * @param string $localeRoutersResourcesFilepathMask
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param string $cacheDir
     */
    public function __construct(
        $localeRoutersResourcesFilepathMask,
        protected readonly ContainerInterface $container,
        protected readonly string $cacheDir,
    ) {
        $this->localeRoutersResourcesFilepathMask = $localeRoutersResourcesFilepathMask;
        $this->routersByLocaleAndHost = [];
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
                $context,
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
