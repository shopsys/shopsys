<?php

namespace Shopsys\FrameworkBundle\Component\Router;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Router\Exception\RouterNotResolvedException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouterFactory;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;

class DomainRouterFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory
     */
    protected $localizedRouterFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouterFactory
     */
    protected $friendlyUrlRouterFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @deprecated This loader is deprecated and will be removed in the next major. Use Symfony\Bundle\FrameworkBundle\Routing\Router instead of Symfony\Component\Routing\Router without this dependency.
     * @var \Symfony\Component\Config\Loader\LoaderInterface
     */
    protected $configLoader;

    /**
     * @var string
     */
    protected $routerConfiguration;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouter[]
     */
    protected $routersByDomainId = [];

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface|null
     */
    protected ?ContainerInterface $container;

    /**
     * @var string|null
     */
    protected ?string $cacheDir;

    /**
     * @param mixed $routerConfiguration
     * @param \Symfony\Component\Config\Loader\LoaderInterface|null $configLoader
     * @param \Shopsys\FrameworkBundle\Component\Router\LocalizedRouterFactory $localizedRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouterFactory $friendlyUrlRouterFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Symfony\Component\DependencyInjection\ContainerInterface|null $container
     * @param string|null $cacheDir
     */
    public function __construct(
        $routerConfiguration,
        ?LoaderInterface $configLoader,
        LocalizedRouterFactory $localizedRouterFactory,
        FriendlyUrlRouterFactory $friendlyUrlRouterFactory,
        Domain $domain,
        RequestStack $requestStack,
        ?ContainerInterface $container = null,
        ?string $cacheDir = null
    ) {
        $this->routerConfiguration = $routerConfiguration;
        $this->configLoader = $configLoader;
        $this->localizedRouterFactory = $localizedRouterFactory;
        $this->domain = $domain;
        $this->friendlyUrlRouterFactory = $friendlyUrlRouterFactory;
        $this->requestStack = $requestStack;
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
        if ($this->container !== null && $this->container !== $container) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->container !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->container = $container;
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
                $friendlyUrlRouter
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
        if ($this->cacheDir === null) {
            $deprecationMessage = sprintf(
                'The argument "$cacheDir" is not provided by constructor in "%s". In the next major it will be required.',
                self::class
            );
            @trigger_error($deprecationMessage, E_USER_DEPRECATED);

            $this->cacheDir = $this->container->getParameter('shopsys.router.domain.cache_dir');
        }

        return new Router(
            $this->container,
            $this->routerConfiguration,
            $this->getRouterOptions(),
            $this->getRequestContextByDomainConfig($domainConfig)
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
