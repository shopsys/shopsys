<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Shopsys\FrameworkBundle\Component\Router\Exception\RouterNotResolvedException;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouterFactory;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class DomainRouterFactory extends AbstractRouterFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouter[]
     */
    protected array $routersByDomainId = [];

    public function __construct(
        protected readonly string $routerConfiguration,
        protected readonly LocalizedRouterFactory $localizedRouterFactory,
        protected readonly FriendlyUrlRouterFactory $friendlyUrlRouterFactory,
        protected readonly Domain $domain,
        protected readonly TransformStringHelper $transformStringHelper,
        RequestStack $requestStack,
        ContainerInterface $container,
        string $cacheDir,
    ) {
        parent::__construct($requestStack, $container, $cacheDir);
    }

    public function getRouter(int $domainId): DomainRouter
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
                $domainConfig,
                $this->transformStringHelper,
            );
        }

        return $this->routersByDomainId[$domainId];
    }

    protected function getBasicRouter(DomainConfig $domainConfig): RouterInterface
    {
        return new Router(
            $this->container,
            $this->routerConfiguration,
            $this->getRouterOptions(),
            $this->getRequestContextByDomainConfig($domainConfig),
        );
    }

    public function getFriendlyUrlRouter(
        DomainConfig $domainConfig,
    ): FriendlyUrlRouter {
        $context = $this->getRequestContextByDomainConfig($domainConfig);

        return $this->friendlyUrlRouterFactory->createRouter($domainConfig, $context);
    }
}
