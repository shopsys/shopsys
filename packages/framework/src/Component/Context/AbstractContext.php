<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Context;

/**
 * Abstract base class for context implementations
 *
 * All contexts must extend this class and implement the abstract methods
 */
abstract class AbstractContext
{
    /**
     * Get the unique identifier for this context
     *
     * @return class-string<static>
     */
    final public function getIdentifier(): string
    {
        return static::class;
    }

    /**
     * Get a human-readable description of this context. It should explain what this context is for and when it is used.
     * This description can help developer to understand the purpose of the context and when it should be applied.
     * It's also used in `shopsys:contexts:list` command
     */
    abstract public function getDescription(): string;

    /**
     * Get required context identifiers that must also match for this context to be valid
     *
     * @return array<class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>>
     */
    public function getRequiredContexts(): array
    {
        return [];
    }

    /**
     * Check if this context matches the current environment/request
     *
     * This method should use dependency injection to access services if needed.
     *
     * @return bool True if this context should be used
     */
    abstract public function matches(): bool;
}
