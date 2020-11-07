<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Router\Exception\LocalizedRoutingConfigFileNotFoundException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

class LocalizedRouterFactory
{
    /**
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
     * @var string
     */
    protected $cacheDir;

    /**
     * @param string $localeRoutersResourcesFilepathMask
     * @param \Symfony\Component\Config\Loader\LoaderInterface $configLoader
     * @param string $cacheDir
     */
    public function __construct($localeRoutersResourcesFilepathMask, LoaderInterface $configLoader, string $cacheDir)
    {
        $this->configLoader = $configLoader;
        $this->localeRoutersResourcesFilepathMask = $localeRoutersResourcesFilepathMask;
        $this->routersByLocaleAndHost = [];
        $this->cacheDir = $cacheDir;
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
                $this->configLoader,
                $this->getLocaleRouterResourceByLocale($locale),
                ['cache_dir' => $this->cacheDir . '/routing/locale_' . $locale],
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
}
