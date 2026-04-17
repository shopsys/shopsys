<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use Override;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;

final class CrudControllerRouteLoader extends Loader
{
    public function __construct(
        private readonly CrudRouteProvider $crudRouteProvider,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function load(mixed $resource, ?string $type = null): RouteCollection
    {
        $routes = new RouteCollection();

        foreach ($this->crudRouteProvider->getAll() as $routeItem) {
            $routes->add($routeItem->getRouteName(), $routeItem->getRoute());
        }

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function supports(mixed $resource, ?string $type = null): bool
    {
        return $type === 'crud_controller';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setResolver(LoaderResolverInterface $resolver): void
    {
    }
}
