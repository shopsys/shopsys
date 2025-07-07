<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Context;

interface ContextResolverInterface
{
    /**
     * Check if the given context identifier is matching in the current environment
     *
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $identifier
     * @return bool True if context would match in current environment
     */
    public function isCurrentContext(string $identifier): bool;

    /**
     * Validate that a context class exists and extends AbstractContext
     *
     * @param string $fcqn Fully qualified class name
     */
    public function validateContextClass(string $fcqn): void;

    /**
     * @return array<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>
     */
    public function getRegisteredContexts(): array;
}
