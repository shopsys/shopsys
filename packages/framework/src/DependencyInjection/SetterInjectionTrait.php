<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\DependencyInjection;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;

trait SetterInjectionTrait
{
    /**
     * @param object $argument
     * @param string $propertyName
     */
    public function setDependency(object $argument, string $propertyName): void
    {
        $caller = debug_backtrace()[1];
        $methodName = $caller['function'];

        if (isset($caller['class'])) {
            $methodName = $caller['class'] . '::' . $methodName;
        }

        if ($this->{$propertyName} !== null && $argument !== $this->{$propertyName}) {
            throw new BadMethodCallException(
                sprintf('Method "%s()" has been already called and cannot be called multiple times.', $methodName),
            );
        }

        if ($this->{$propertyName} !== null) {
            return;
        }

        DeprecationHelper::triggerSetterInjection($methodName);

        $this->{$propertyName} = $argument;
    }
}
