<?php

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouterFactory;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

class DomainRouterFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory
     */
    private $localizedRouterFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouterFactory
     */
    private $friendlyUrlRouterFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Symfony\Component\Config\Loader\DelegatingLoader
     */
    private $delegatingLoader;

    /**
     * @var string
     */
    private $routerConfiguration;

    /**
     * @var \Symfony\Component\Routing\Router[]
     */
    private $routersByDomainId = [];

    public function __construct(
        $routerConfiguration,
        DelegatingLoader $delegatingLoader,
        LocalizedRouterFactory $localizedRouterFactory,
        FriendlyUrlRouterFactory $friendlyUrlRouterFactory,
        Domain $domain
    ) {
        $this->routerConfiguration = $routerConfiguration;
        $this->delegatingLoader = $delegatingLoader;
        $this->localizedRouterFactory = $localizedRouterFactory;
        $this->domain = $domain;
        $this->friendlyUrlRouterFactory = $friendlyUrlRouterFactory;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Router\DomainRouter
     */
    public function getRouter($domainId)
    {
        if (!array_key_exists($domainId, $this->routersByDomainId)) {
            try {
                $domainConfig = $this->domain->getDomainConfigById($domainId);
            } catch (\Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException $exception) {
                throw new \Shopsys\FrameworkBundle\Component\Router\Exception\RouterNotResolvedException('', $exception);
            }
            $context = $this->getRequestContextByDomainConfig($domainConfig);
            $basicRouter = $this->getBasicRouter($domainConfig);
            $localizedRouter = $this->localizedRouterFactory->getRouter($domainConfig->getLocale(), $context);
            $friendlyUrlRouter = $this->friendlyUrlRouterFactory->createRouter($domainConfig, $context);
            $this->routersByDomainId[$domainId] = new DomainRouter(
                $context,
                $basicRouter,
                $localizedRouter,
                $friendlyUrlRouter
            );
        }

        return $this->routersByDomainId[$domainId];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Symfony\Component\Routing\Router
     */
    private function getBasicRouter(DomainConfig $domainConfig)
    {
        return new Router(
            $this->delegatingLoader,
            $this->routerConfiguration,
            [],
            $this->getRequestContextByDomainConfig($domainConfig)
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Symfony\Component\Routing\RequestContext
     */
    private function getRequestContextByDomainConfig(DomainConfig $domainConfig)
    {
        $urlComponents = parse_url($domainConfig->getUrl());
        $requestContext = new RequestContext();

        if (array_key_exists('path', $urlComponents)) {
            $requestContext->setBaseUrl($urlComponents['path']);
        }

        $requestContext->setScheme($urlComponents['scheme']);
        $requestContext->setHost($urlComponents['host']);

        if (array_key_exists('port', $urlComponents)) {
            if ($urlComponents['scheme'] === 'http') {
                $requestContext->setHttpPort($urlComponents['port']);
            } elseif ($urlComponents['scheme'] === 'https') {
                $requestContext->setHttpsPort($urlComponents['port']);
            }
        }

        return $requestContext;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter
     */
    public function getFriendlyUrlRouter(DomainConfig $domainConfig)
    {
        $context = $this->getRequestContextByDomainConfig($domainConfig);

        return $this->friendlyUrlRouterFactory->createRouter($domainConfig, $context);
    }
}
