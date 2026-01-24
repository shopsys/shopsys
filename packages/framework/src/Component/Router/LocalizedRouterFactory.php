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
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router[][]
     */
    protected array $routersByLocaleAndHost;

    public function __construct(
        protected string $localeRoutersResourcesFilepathMask,
        protected readonly ContainerInterface $container,
        protected readonly string $cacheDir,
    ) {
        $this->routersByLocaleAndHost = [];
    }

    public function getRouter(string $locale, RequestContext $context): Router
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

    protected function getLocaleRouterResourceByLocale(string $locale): string
    {
        return str_replace('*', $locale, $this->localeRoutersResourcesFilepathMask);
    }

    protected function getRoutingCacheDir(string $locale): string
    {
        return $this->cacheDir . '/' . $locale;
    }

    protected function getRouterOptions(string $locale): array
    {
        $options = [];

        if ($this->container->getParameter('kernel.environment') !== EnvironmentType::DEVELOPMENT) {
            $options['cache_dir'] = $this->getRoutingCacheDir($locale);
        }

        return $options;
    }
}
