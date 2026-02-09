<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role\Event;

use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Event dispatched by RolesCommand when displaying detailed role information
 * Allows other bundles to add custom output when roles are displayed in detail view
 */
class RolesCommandDetailEvent extends Event
{
    /**
     * @var array<callable>
     */
    protected array $renderCallbacks = [];

    /**
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $context
     */
    public function __construct(
        protected readonly Role $role,
        protected readonly string $context,
    ) {
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    /**
     * @return class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * Add a render callback that will be executed by the command
     * The callback receives SymfonyStyle and OutputInterface as parameters
     *
     * @param callable(\Symfony\Component\Console\Style\SymfonyStyle, \Symfony\Component\Console\Output\OutputInterface): void $callback
     */
    public function addRenderCallback(callable $callback): void
    {
        $this->renderCallbacks[] = $callback;
    }

    /**
     * Get all render callbacks
     *
     * @return array<callable>
     */
    public function getRenderCallbacks(): array
    {
        return $this->renderCallbacks;
    }
}
