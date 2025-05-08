<?php

use App\Component\FriendlyUrl\FriendlyUrlRouteEnum;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Friendly URL routing ignores the required "path" attribute.
 * @see \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlRouter
 */
$routeCollection = new RouteCollection();

foreach (FriendlyUrlRouteEnum::cases() as $case) {
    $route = new Route('friendly-url', options: FriendlyUrlRouteEnum::getOptionsForCase($case));
    $routeCollection->add($case->value, $route);
}

return $routeCollection;
