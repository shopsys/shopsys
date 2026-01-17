<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component;

use Override;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader as BaseAttributeRouteControllerLoader;

class AttributeRouteControllerLoader extends BaseAttributeRouteControllerLoader
{
    #[Override]
    protected function getDefaultRouteName(ReflectionClass $class, ReflectionMethod $method): string
    {
        return static::replacePartOfTheRouteName(parent::getDefaultRouteName($class, $method));
    }

    public static function replacePartOfTheRouteName(string $routeName): string
    {
        return preg_replace('/^(app_|shopsys_framework_|shopsys_frontendapi_)/', '', $routeName);
    }

    public function getRouteName(ReflectionClass $class, ReflectionMethod $method): string
    {
        return $this->getDefaultRouteName($class, $method);
    }
}
