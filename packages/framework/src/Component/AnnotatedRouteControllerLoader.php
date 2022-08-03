<?php

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
     * @template T of object
     * @param \ReflectionClass<T> $class
     * @param \ReflectionMethod $method
     * @return string The default route name
     */
    protected function getDefaultRouteName(ReflectionClass $class, ReflectionMethod $method)
    {
        $routeName = parent::getDefaultRouteName($class, $method);

        return preg_replace('/^(app_|shopsys_framework_)/', '', $routeName);
    }
}
