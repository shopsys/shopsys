<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component;

use Override;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Routing\AttributeRouteControllerLoader as BaseAttributeRouteControllerLoader;

class AttributeRouteControllerLoader extends BaseAttributeRouteControllerLoader
{
    /**
     * @param \ReflectionClass $class
     * @param \ReflectionMethod $method
     * @return string
     */
    #[Override]
    protected function getDefaultRouteName(ReflectionClass $class, ReflectionMethod $method): string
    {
        return static::replacePartOfTheRouteName(parent::getDefaultRouteName($class, $method));
    }

    /**
     * @param string $routeName
     * @return string
     */
    public static function replacePartOfTheRouteName(string $routeName): string
    {
        return preg_replace('/^(app_|shopsys_framework_)/', '', $routeName);
    }

    /**
     * @param \ReflectionClass $class
     * @param \ReflectionMethod $method
     * @return string
     */
    public function getRouteName(ReflectionClass $class, ReflectionMethod $method): string
    {
        return $this->getDefaultRouteName($class, $method);
    }
}
