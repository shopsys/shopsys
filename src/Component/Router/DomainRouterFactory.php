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
 * @property \App\Component\Domain\Domain $domain
 */
class DomainRouterFactory extends BaseDomainRouterFactory
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(
        string $routerConfiguration,
        LoaderInterface $configLoader,
        LocalizedRouterFactory $localizedRouterFactory,
        FriendlyUrlRouterFactory $friendlyUrlRouterFactory,
        Domain $domain,
        RequestStack $requestStack,
        ContainerInterface $container
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
            ['resource_type' => 'service'],
            $this->getRequestContextByDomainConfig($domainConfig)
        );
    }
}
