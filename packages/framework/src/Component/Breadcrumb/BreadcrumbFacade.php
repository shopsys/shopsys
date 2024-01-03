<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Breadcrumb;

use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BreadcrumbFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Breadcrumb\BreadcrumbResolver $breadcrumbResolver
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        protected readonly BreadcrumbResolver $breadcrumbResolver,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param int $id
     * @param string $routeName
     * @param int $domainId
     * @param string|null $locale
     * @return array<int, array{name: string, slug: string}>
     */
    public function getBreadcrumbOnDomain(int $id, string $routeName, int $domainId, ?string $locale = null): array
    {
        $breadcrumbItems = $this->breadcrumbResolver->resolveBreadcrumbItemsOnDomain(
            $domainId,
            $routeName,
            ['id' => $id],
            $locale,
        );

        $domainRouter = $this->domainRouterFactory->getRouter($domainId);

        $breadcrumb = [];

        foreach ($breadcrumbItems as $breadcrumbItem) {
            $breadcrumbItemRouteName = $breadcrumbItem->getRouteName() ?? $routeName;
            $breadcrumbItemRouteParams = $breadcrumbItem->getRouteName() ? $breadcrumbItem->getRouteParameters() : ['id' => $id];

            $breadcrumb[] = [
                'name' => $breadcrumbItem->getName(),
                'slug' => $domainRouter->generate($breadcrumbItemRouteName, $breadcrumbItemRouteParams),
            ];
        }

        return $breadcrumb;
    }

    /**
     * @param int $id
     * @param string $routeName
     * @return array<int, array{name: string, slug: string}>
     */
    public function getBreadcrumbOnCurrentDomain(int $id, string $routeName): array
    {
        $breadcrumbItems = $this->breadcrumbResolver->resolveBreadcrumbItems(
            $routeName,
            ['id' => $id],
        );

        return array_map(
            function (BreadcrumbItem $breadcrumbItem) use ($id, $routeName) {
                $breadcrumbItemRouteName = $breadcrumbItem->getRouteName() ?? $routeName;
                $breadcrumbItemRouteParams = $breadcrumbItem->getRouteName() ? $breadcrumbItem->getRouteParameters() : ['id' => $id];

                return [
                    'name' => $breadcrumbItem->getName(),
                    'slug' => $this->urlGenerator->generate($breadcrumbItemRouteName, $breadcrumbItemRouteParams),
                ];
            },
            $breadcrumbItems,
        );
    }
}
