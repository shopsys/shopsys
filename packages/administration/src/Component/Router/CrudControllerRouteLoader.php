<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use Shopsys\AdministrationBundle\Component\Config\CrudConfigProvider;
use Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem;
use Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionRegistry;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\Routing\RouteCollection;

final class CrudControllerRouteLoader implements LoaderInterface
{
    private bool $loaded = false;

    /**
     * @param \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionRegistry $registry
     * @param \Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider $crudRouteProvider
     */
    public function __construct(
        private readonly CrudControllerDefinitionRegistry $registry,
        private readonly CrudRouteProvider $crudRouteProvider,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function load($resource, string $type = null)
    {
        if ($this->loaded === true) {
            // Instead of throwing an exception, return an empty RouteCollection
            return new RouteCollection();
        }

        $routes = new RouteCollection();

        foreach ($this->registry->getItems() as $item) {
            $this->addRoutesForController($routes, $item);
        }

        $this->loaded = true;

        return $routes;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, string $type = null)
    {
        return $type === 'crud_controller';
    }

    /**
     * {@inheritdoc}
     */
    public function getResolver()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
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

        foreach ($config->getDefaultActions() as $pageType) {
            $routeItem = $this->crudRouteProvider->generate($item, $pageType);

            $routes->add($routeItem->getRouteName(), $routeItem->getRoute());
        }
    }
}
