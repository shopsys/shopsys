<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlIsNotMultidomainException;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class FriendlyUrlFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Component\String\TransformStringHelper $transformStringHelper
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly TransformStringHelper $transformStringHelper,
        protected readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param int $domainId
     * @param string $slug
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl
     */
    public function create(
        string $routeName,
        int $entityId,
        int $domainId,
        string $slug,
    ): FriendlyUrl {
        $entityClassName = $this->entityNameResolver->resolve(FriendlyUrl::class);

        return new $entityClassName($routeName, $entityId, $domainId, $slug);
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param string $entityName
     * @param int $domainId
     * @param int|null $indexPostfix
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl|null
     */
    public function createIfValid(
        string $routeName,
        int $entityId,
        string $entityName,
        int $domainId,
        ?int $indexPostfix = null,
    ): ?FriendlyUrl {
        if ($entityName === '') {
            return null;
        }

        $nameForUrl = $entityName . ($indexPostfix === null ? '' : '-' . $indexPostfix);
        $slug = $this->transformStringHelper->stringToFriendlyUrlSlug($nameForUrl);

        return $this->create($routeName, $entityId, $domainId, $slug);
    }

    /**
     * @param string $routeName
     * @param int $entityId
     * @param string[] $namesByLocale
     * @return \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrl[]
     */
    public function createForAllDomains(
        string $routeName,
        int $entityId,
        array $namesByLocale,
    ): array {
        $friendlyUrls = [];

        if ($this->isRouteMultidomain($routeName) === false) {
            throw new FriendlyUrlIsNotMultidomainException($routeName);
        }

        foreach ($this->domain->getAll() as $domainConfig) {
            if (array_key_exists($domainConfig->getLocale(), $namesByLocale)) {
                $friendlyUrl = $this->createIfValid(
                    $routeName,
                    $entityId,
                    (string)$namesByLocale[$domainConfig->getLocale()],
                    $domainConfig->getId(),
                );

                if ($friendlyUrl !== null) {
                    $friendlyUrls[] = $friendlyUrl;
                }
            }
        }

        return $friendlyUrls;
    }

    /**
     * @param string $routeName
     * @return bool
     */
    protected function isRouteMultidomain(string $routeName): bool
    {
        $friendlyUrlRouter = $this->domainRouterFactory->getFriendlyUrlRouter($this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID));
        $routeCollection = $friendlyUrlRouter->getRouteCollection();
        $route = $routeCollection->get($routeName);

        if ($route === null) {
            throw new RouteNotFoundException('Route "' . $routeName . '" not found.');
        }

        return $route->getOption(FriendlyUrlRouter::ROUTE_OPTION_MULTIDOMAIN) ?? true;
    }
}
