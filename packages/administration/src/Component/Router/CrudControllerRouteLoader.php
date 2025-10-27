<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;

final class CrudControllerRouteLoader implements LoaderInterface
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry $crudControllerRegistry
     * @param \Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider $crudRouteProvider
     */
    public function __construct(
        private readonly CrudControllerRegistry $crudControllerRegistry,
        private readonly CrudRouteProvider $crudRouteProvider,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function load($resource, ?string $type = null)
    {
        $routes = new RouteCollection();

        foreach ($this->crudControllerRegistry->getItems() as $item) {
            $this->addRoutesForController($routes, $item);
        }

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function supports($resource, ?string $type = null)
    {
        return $type === 'crud_controller';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getResolver()
    {
        /* @phpstan-ignore-next-line */
        return null;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setResolver(LoaderResolverInterface $resolver)
    {
        // No implementation needed
    }

    /**
     * @param \Symfony\Component\Routing\RouteCollection $routes
     * @param \Shopsys\AdministrationBundle\Component\Crud\Definition $item
     */
    private function addRoutesForController(RouteCollection $routes, Definition $item): void
    {
        foreach ($item->getConfig()->getActions() as $actionType) {
            $routeItem = $this->crudRouteProvider->generate($item, $actionType);

            $route = $routeItem->getRoute();

            $route->setDefault('_crud_controller', true);
            $route->setDefault('_crud_action', $actionType->value);
            $route->setDefault('_crud_role_constant', $item->getRoleConstant());

            $routes->add($routeItem->getRouteName(), $route);
        }
    }
}
