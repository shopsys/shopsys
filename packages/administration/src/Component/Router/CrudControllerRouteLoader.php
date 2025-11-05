<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use Override;
use Shopsys\AdministrationBundle\Component\Config\CrudConfigProvider;
use Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem;
use Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionRegistry;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;

final class CrudControllerRouteLoader implements LoaderInterface
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionRegistry $registry
     * @param \Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider $crudRouteProvider
     * @param \Shopsys\AdministrationBundle\Component\Config\CrudConfigProvider $crudConfigProvider
     */
    public function __construct(
        private readonly CrudControllerDefinitionRegistry $registry,
        private readonly CrudRouteProvider $crudRouteProvider,
        private readonly CrudConfigProvider $crudConfigProvider,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function load($resource, ?string $type = null)
    {
        $routes = new RouteCollection();

        foreach ($this->registry->getItems() as $item) {
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
     * @param \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem $item
     */
    private function addRoutesForController(RouteCollection $routes, CrudControllerDefinitionItem $item): void
    {
        $config = $this->crudConfigProvider->getConfig($item);

        if ($config->isFullDisabled()) {
            return;
        }

        foreach ($config->getActions() as $actionType) {
            $routeItem = $this->crudRouteProvider->generate($item, $actionType);

            $routes->add($routeItem->getRouteName(), $routeItem->getRoute());
        }
    }
}
