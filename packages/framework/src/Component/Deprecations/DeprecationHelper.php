<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Deprecations;

use const E_USER_DEPRECATED;

final class DeprecationHelper
{
    /**
     * @param string $message
     * @param mixed $arguments
     */
    public static function trigger(string $message, ...$arguments): void
    {
        @trigger_error(
            vsprintf($message, $arguments),
            E_USER_DEPRECATED
        );
    }

    /**
     * @param string $methodName
     */
    public static function triggerSetterInjection(string $methodName): void
    {
        self::trigger(
            'The "%s()" method is deprecated and will be removed in the next major. Use the constructor injection instead.',
            $methodName
        );
    }

    /**
     * @param string $className
     * @param string|null $replacement
     */
    public static function triggerClass(string $className, ?string $replacement = null): void
    {
        $message = sprintf(
            'The "%s" class is deprecated and will be removed in the next major.',
            $className
        );

        if ($replacement !== null) {
            $message .= sprintf(' Use "%s" instead.', $replacement);
        } else {
            $message .= ' No replacement suggested.';
        }

        self::trigger($message);
    }

    /**
     * @param string $methodName
     * @param string|null $replacement
     */
    public static function triggerMethod(string $methodName, ?string $replacement = null): void
    {
        $message = sprintf(
            'The "%s()" method is deprecated and will be removed in the next major.',
            $methodName
        );

        if ($replacement !== null) {
            $message .= sprintf(' Use "%s()" instead.', $replacement);
        } else {
            $message .= ' No replacement suggested.';
        }

        self::trigger($message);
    }

    /**
     * @param string $className
     */
    public static function triggerAbstractClass(string $className): void
    {
        self::trigger(
            'Class "%s" will be changed to abstract class in next major version. Extend this class to your project and implement corresponding methods instead.',
            $className
        );
    }

    /**
     * @param string $methodName
     */
    public static function triggerAbstractMethod(string $methodName): void
    {
        self::trigger(
            'Method "%s()" will be changed to abstract in next major version. Extend this class to your project and implement method by yourself instead.',
            $methodName
        );
    }
}
