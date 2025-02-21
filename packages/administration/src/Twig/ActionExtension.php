<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Twig;

use InvalidArgumentException;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\ActionRouteInterface;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\CrudActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\RouteActionRouteData;
use Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\UrlActionRouteData;
use Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionRegistry;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ActionExtension extends AbstractExtension
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionRegistry $crudControllerDefinitionRegistry
     * @param \Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider $crudRouteProvider
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(
        private readonly CrudControllerDefinitionRegistry $crudControllerDefinitionRegistry,
        private readonly CrudRouteProvider $crudRouteProvider,
        private readonly RouterInterface $router,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'action_url',
                $this->generateActionUrl(...),
            ),
        ];
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Config\Action\Builder\ActionRoute\ActionRouteInterface|null $actionRoute
     * @param object|null $entity
     * @return string
     */
    private function generateActionUrl(?ActionRouteInterface $actionRoute, ?object $entity): string
    {
        if ($actionRoute === null) {
            return 'javascript:void(0)';
        }

        if ($actionRoute instanceof UrlActionRouteData) {
            return $actionRoute->getUrl($entity);
        }

        if ($actionRoute instanceof CrudActionRouteData) {
            $routeItem = $this->crudRouteProvider->generate(
                $this->crudControllerDefinitionRegistry->getItem($actionRoute->getCrudController()),
                $actionRoute->getActionType(),
            );

            $parameters = $actionRoute->getId($entity) !== null ? ['id' => $actionRoute->getId($entity)] : [];

            return $this->router->generate($routeItem->getRouteName(), $parameters);
        }

        if ($actionRoute instanceof RouteActionRouteData) {
            return $this->router->generate($actionRoute->getRouteName(), $actionRoute->getRouteParameters($entity));
        }

        throw new InvalidArgumentException('Action has invalid route type');
    }
}
