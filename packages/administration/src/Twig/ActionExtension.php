<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Twig;

use InvalidArgumentException;
use Override;
use Shopsys\AdministrationBundle\Component\Action\RouteData\ActionRouteInterface;
use Shopsys\AdministrationBundle\Component\Action\RouteData\CrudActionRouteData;
use Shopsys\AdministrationBundle\Component\Action\RouteData\RouteActionRouteData;
use Shopsys\AdministrationBundle\Component\Action\RouteData\UrlActionRouteData;
use Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry;
use Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ActionExtension extends AbstractExtension
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Crud\CrudControllerRegistry $crudControllerRegistry
     * @param \Shopsys\AdministrationBundle\Component\Router\CrudRouteProvider $crudRouteProvider
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface $routeAccessChecker
     */
    public function __construct(
        private readonly CrudControllerRegistry $crudControllerRegistry,
        private readonly CrudRouteProvider $crudRouteProvider,
        private readonly RouterInterface $router,
        private readonly RouteAccessCheckerInterface $routeAccessChecker,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
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
     * @return string|null Return null if user does not have access to the action
     */
    private function generateActionUrl(?ActionRouteInterface $actionRoute, mixed $data): ?string
    {
        if ($actionRoute === null) {
            return 'javascript:void(0)';
        }

        if ($actionRoute instanceof UrlActionRouteData) {
            return $actionRoute->getUrl($data);
        }

        if ($actionRoute instanceof CrudActionRouteData) {
            $routeItem = $this->crudRouteProvider->generate(
                $this->crudControllerRegistry->getItem($actionRoute->getCrudController()),
                $actionRoute->getActionType(),
            );

            $parameters = $actionRoute->getId($data) !== null ? ['id' => $actionRoute->getId($data)] : [];

            return $this->generateByRoute($routeItem->getRouteName(), $parameters);
        }

        if ($actionRoute instanceof RouteActionRouteData) {
            return $this->generateByRoute($actionRoute->getRouteName(), $actionRoute->getRouteParameters($data));
        }

        throw new InvalidArgumentException('Action has invalid route type');
    }

    /**
     * @param string $routeName
     * @param array $parameters
     * @return string|null
     */
    private function generateByRoute(string $routeName, array $parameters): ?string
    {
        if (!$this->routeAccessChecker->hasAccess($routeName, HttpMethod::GET)) {
            return null;
        }

        return $this->router->generate($routeName, $parameters);
    }
}
