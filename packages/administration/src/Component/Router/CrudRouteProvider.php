<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Router;

use Shopsys\AdministrationBundle\Component\Config\PageType;
use Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem;
use Symfony\Component\Routing\Route;

final class CrudRouteProvider
{
    private const PAGES_WITH_ID = [PageType::EDIT, PageType::DELETE, PageType::DETAIL];

    /**
     * @param \Shopsys\AdministrationBundle\Component\Registry\CrudControllerDefinitionItem $item
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @return \Shopsys\AdministrationBundle\Component\Router\CrudRouteItem
     */
    public function generate(CrudControllerDefinitionItem $item, PageType $pageType): CrudRouteItem
    {
        return new CrudRouteItem(
            controller: $this->generateController($item->controllerClass, $pageType),
            route: $this->generateRoute($item->shortEntityClass, $item->controllerClass, $pageType),
            routeName: $this->generateRouteName($item->shortEntityClass, $pageType),
            pageType: $pageType,
        );
    }

    /**
     * @param string $shortEntityClass
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @return string
     */
    private function generateRouteName(string $shortEntityClass, PageType $pageType): string
    {
        return 'admin_crud_' . strtolower(str_replace('\\', '_', $shortEntityClass)) . '_' . $pageType->value;
    }

    /**
     * @param string $shortEntityClass
     * @param string $controllerClass
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @return \Symfony\Component\Routing\Route
     */
    private function generateRoute(string $shortEntityClass, string $controllerClass, PageType $pageType): Route
    {
        $withId = in_array($pageType, self::PAGES_WITH_ID, true);

        return new Route(
            '/' . strtolower($shortEntityClass) . '/' . $pageType->value . ($withId ? '/{id}' : ''),
            [
                '_controller' => $this->generateController($controllerClass, $pageType),
            ],
        );
    }

    /**
     * @param string $controllerClass
     * @param \Shopsys\AdministrationBundle\Component\Config\PageType $pageType
     * @return string
     */
    private function generateController(string $controllerClass, PageType $pageType): string
    {
        return sprintf('%s::%sAction', $controllerClass, $pageType->value);
    }
}
