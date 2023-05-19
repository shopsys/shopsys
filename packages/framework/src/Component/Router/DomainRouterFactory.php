<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Router\Exception\RouterNotResolvedException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouterFactory;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;

class DomainRouterFactory
{
    protected string $routerConfiguration;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouter[]
     */
    protected array $routersByDomainId = [];

    /**
     * @param mixed $routerConfiguration
     * @param \Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory $localizedRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouterFactory $friendlyUrlRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param string $cacheDir
     */
    public function __construct(
        $routerConfiguration,
        protected readonly LocalizedRouterFactory $localizedRouterFactory,
        protected readonly FriendlyUrlRouterFactory $friendlyUrlRouterFactory,
        protected readonly Domain $domain,
        protected readonly RequestStack $requestStack,
        protected readonly ContainerInterface $container,
        protected readonly string $cacheDir,
    ) {
        $this->routerConfiguration = $routerConfiguration;
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
            } catch (InvalidDomainIdException $exception) {
                throw new RouterNotResolvedException('', $exception);
            }
            $context = $this->getRequestContextByDomainConfig($domainConfig);
            $basicRouter = $this->getBasicRouter($domainConfig);
            $localizedRouter = $this->localizedRouterFactory->getRouter($domainConfig->getLocale(), $context);
            $friendlyUrlRouter = $this->friendlyUrlRouterFactory->createRouter($domainConfig, $context);
            $this->routersByDomainId[$domainId] = new DomainRouter(
                $context,
                $basicRouter,
                $localizedRouter,
                $friendlyUrlRouter,
            );
        }

        return $this->routersByDomainId[$domainId];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Symfony\Component\Routing\Router
     */
    protected function getBasicRouter(DomainConfig $domainConfig)
    {
        return new Router(
            $this->container,
            $this->routerConfiguration,
            $this->getRouterOptions(),
            $this->getRequestContextByDomainConfig($domainConfig),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return \Symfony\Component\Routing\RequestContext
     */
    protected function getRequestContextByDomainConfig(DomainConfig $domainConfig)
    {
        $urlComponents = parse_url($domainConfig->getUrl());
        $requestContext = new RequestContext();
        $request = $this->requestStack->getCurrentRequest();

        if ($request !== null) {
            $requestContext->fromRequest($request);
        }

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

    /**
     * @return array
     */
    protected function getRouterOptions(): array
    {
        $options = ['resource_type' => 'service'];

        if ($this->container->getParameter('kernel.environment') !== EnvironmentType::DEVELOPMENT) {
            $options['cache_dir'] = $this->cacheDir;
        }

        return $options;
    }
}
