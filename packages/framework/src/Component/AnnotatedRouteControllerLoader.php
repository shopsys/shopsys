<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component;

use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Routing\AnnotatedRouteControllerLoader as BaseAnnotatedRouteControllerLoader;

/**
 * AnnotatedRouteControllerLoader
 */
class AnnotatedRouteControllerLoader extends BaseAnnotatedRouteControllerLoader
{
    /**
     * Makes the default route name shorter by removing some obvious parts.
     *
     * @param \ReflectionClass $class
     * @param \ReflectionMethod $method
     * @return string The default route name
     */
    protected function getDefaultRouteName(ReflectionClass $class, ReflectionMethod $method): string
    {
        $routeName = parent::getDefaultRouteName($class, $method);

        return preg_replace('/^(app_|shopsys_framework_)/', '', $routeName);
    }
}
