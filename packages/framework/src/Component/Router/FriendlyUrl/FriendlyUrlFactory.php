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
    public function __construct(
        protected readonly Domain $domain,
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly TransformStringHelper $transformStringHelper,
        protected readonly DomainRouterFactory $domainRouterFactory,
    ) {
    }

    public function create(
        string $routeName,
        int $entityId,
        int $domainId,
        string $slug,
    ): FriendlyUrl {
        $entityClassName = $this->entityNameResolver->resolve(FriendlyUrl::class);

        return new $entityClassName($routeName, $entityId, $domainId, $slug);
    }

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
