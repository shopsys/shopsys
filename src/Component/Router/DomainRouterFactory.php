<?php

declare(strict_types=1);

namespace App\Component\Router;

use Psr\Container\ContainerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory as BaseDomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 */
class DomainRouterFactory extends BaseDomainRouterFactory
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * @var string
     */
    private string $cacheDir;

    /**
     * @param string $routerConfiguration
     * @param \Symfony\Component\Config\Loader\LoaderInterface $configLoader
     * @param \Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory $localizedRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouterFactory $friendlyUrlRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Psr\Container\ContainerInterface $container
     * @param string $cacheDir
     */
    public function __construct(
        string $routerConfiguration,
        LoaderInterface $configLoader,
        LocalizedRouterFactory $localizedRouterFactory,
        FriendlyUrlRouterFactory $friendlyUrlRouterFactory,
        Domain $domain,
        RequestStack $requestStack,
        ContainerInterface $container,
        string $cacheDir
    ) {
        parent::__construct(
            $routerConfiguration,
            $configLoader,
            $localizedRouterFactory,
            $friendlyUrlRouterFactory,
            $domain,
            $requestStack
        );

        $this->container = $container;
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected function getBasicRouter(DomainConfig $domainConfig)
    {
        return new Router(
            $this->container,
            $this->routerConfiguration,
            [
                'resource_type' => 'service',
                'cache_dir' => $this->cacheDir . '/routing/domain' . $domainConfig->getId(),
            ],
            $this->getRequestContextByDomainConfig($domainConfig)
        );
    }
}
