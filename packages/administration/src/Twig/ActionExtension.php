<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Twig;

use InvalidArgumentException;
use Shopsys\AdministrationBundle\Component\Action\RouteData\ActionRouteInterface;
use Shopsys\AdministrationBundle\Component\Action\RouteData\CrudActionRouteData;
use Shopsys\AdministrationBundle\Component\Action\RouteData\RouteActionRouteData;
use Shopsys\AdministrationBundle\Component\Action\RouteData\UrlActionRouteData;
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
     * @param \Shopsys\AdministrationBundle\Component\Action\RouteData\ActionRouteInterface|null $actionRoute
     * @param mixed $data
     * @return string
     */
    private function generateActionUrl(?ActionRouteInterface $actionRoute, mixed $data): string
    {
        if ($actionRoute === null) {
            return 'javascript:void(0)';
        }

        if ($actionRoute instanceof UrlActionRouteData) {
            return $actionRoute->getUrl($data);
        }

        if ($actionRoute instanceof CrudActionRouteData) {
            $routeItem = $this->crudRouteProvider->generate(
                $this->crudControllerDefinitionRegistry->getItem($actionRoute->getCrudController()),
                $actionRoute->getActionType(),
            );

            $parameters = $actionRoute->getId($data) !== null ? ['id' => $actionRoute->getId($data)] : [];

            return $this->router->generate($routeItem->getRouteName(), $parameters);
        }

        if ($actionRoute instanceof RouteActionRouteData) {
            return $this->router->generate($actionRoute->getRouteName(), $actionRoute->getRouteParameters($data));
        }

        throw new InvalidArgumentException('Action has invalid route type');
    }
}
