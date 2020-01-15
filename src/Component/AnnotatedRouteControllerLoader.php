<?php

declare(strict_types=1);

namespace App\Component;

use Symfony\Bundle\FrameworkBundle\Routing\AnnotatedRouteControllerLoader as BaseAnnotatedRouteControllerLoader;

class AnnotatedRouteControllerLoader extends BaseAnnotatedRouteControllerLoader
{
    /**
     * @param \ReflectionClass $class
     * @param \ReflectionMethod $method
     * @return string|string[]|null
     */
    protected function getDefaultRouteName(\ReflectionClass $class, \ReflectionMethod $method)
    {
        $routeName = parent::getDefaultRouteName($class, $method);

        return preg_replace('/^(app_|shopsys_framework_)/', '', $routeName);
    }
}
