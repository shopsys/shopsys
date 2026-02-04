<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use Override;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Crud\Definition;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\RouteCollection;

final class CrudControllerRouteLoader extends Loader
{
    public const string IS_CRUD_CONTROLLER = '_crud_controller';
    public const string CRUD_ACTION = '_crud_action';
    public const string CRUD_ROLE_CONSTANT = '_crud_role_constant';

    public function __construct(
        private readonly CrudControllerRegistry $crudControllerRegistry,
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

        foreach ($this->crudControllerRegistry->getItems() as $item) {
            $this->addRoutesForController($routes, $item);
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
    public function getResolver(): ?LoaderResolverInterface
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setResolver(LoaderResolverInterface $resolver): void
    {
        // No implementation needed
    }

    private function addRoutesForController(RouteCollection $routes, Definition $item): void
    {
        foreach ($item->getConfig()->getActions() as $actionType) {
            $routeItem = $this->crudRouteProvider->generate($item, $actionType);

            $route = $routeItem->getRoute();

            $route->setDefault(self::IS_CRUD_CONTROLLER, true);
            $route->setDefault(self::CRUD_ACTION, $actionType->value);
            $route->setDefault(self::CRUD_ROLE_CONSTANT, $item->getRoleConstant());

            $routes->add($routeItem->getRouteName(), $route);
        }
    }
}
