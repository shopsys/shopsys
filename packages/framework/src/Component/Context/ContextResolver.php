<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Context;

use InvalidArgumentException;
use Override;
use Webmozart\Assert\Assert;

final class ContextResolver implements ContextResolverInterface
{
    /**
     * @var array<string, \Shopsys\FrameworkBundle\Component\Context\AbstractContext>
     */
    private array $contexts = [];

    /**
     * @var array<string, bool> Cached matching results for each context (empty until first matching check)
     */
    private array $contextMatchingResults = [];

    /**
     * @param iterable<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $contexts
     */
    public function __construct(iterable $contexts = [])
    {
        foreach ($contexts as $context) {
            $this->addContext($context);
        }


        foreach ($this->contexts as $context) {
            $this->validateContexts($context, []);
        }
    }

    #[Override]
    public function validateContextClass(string $fcqn): void
    {
        Assert::classExists($fcqn);
        Assert::true(
            is_subclass_of($fcqn, AbstractContext::class),
            sprintf('Context "%s" must extend "%s"', $fcqn, AbstractContext::class),
        );
    }

    #[Override]
    public function isCurrentContext(string $identifier): bool
    {
        $this->validateContextClass($identifier);
        $this->buildMatchingResults();

        return $this->contextMatchingResults[$identifier] ?? throw new InvalidArgumentException(
            sprintf('Context "%s" is not registered', $identifier),
        );
    }

    #[Override]
    public function getRegisteredContexts(): array
    {
        return array_values($this->contexts);
    }

    /**
     * @throws \InvalidArgumentException if context with same identifier already exists
     */
    private function addContext(AbstractContext $context): void
    {
        $identifier = $context->getIdentifier();
        $this->validateContextClass($identifier);

        if (isset($this->contexts[$identifier])) {
            throw new InvalidArgumentException(
                sprintf('Context with identifier "%s" is already registered', $identifier),
            );
        }

        $this->contexts[$identifier] = $context;
    }

    /**
     * @param array<string> $visitedStack Stack of visited context identifiers for cycle detection
     * @throws \InvalidArgumentException if circular dependency is detected or required context is missing
     */
    private function validateContexts(AbstractContext $context, array $visitedStack): void
    {
        $identifier = $context->getIdentifier();

        if (in_array($identifier, $visitedStack, true)) {
            $cycleStart = array_search($identifier, $visitedStack, true);
            $dependencyPath = array_slice($visitedStack, $cycleStart !== false ? $cycleStart : 0);
            $dependencyPath[] = $identifier;

            throw new InvalidArgumentException(
                sprintf(
                    'Circular dependency detected: %s',
                    implode(' → ', $dependencyPath),
                ),
            );
        }

        $visitedStack[] = $identifier;

        foreach ($context->getRequiredContexts() as $requiredContextClass) {
            $this->validateContextClass($requiredContextClass);

            if (!isset($this->contexts[$requiredContextClass])) {
                throw new InvalidArgumentException(
                    sprintf('Required context "%s" is not registered', $requiredContextClass),
                );
            }

            $this->validateContexts($this->contexts[$requiredContextClass], $visitedStack);
        }
    }

    private function buildMatchingResults(): void
    {
        if ($this->contextMatchingResults !== []) {
            return;
        }

        foreach ($this->contexts as $context) {
            if (isset($this->contextMatchingResults[$context->getIdentifier()])) {
                continue;
            }

            $this->contextMatchingResults[$context->getIdentifier()] = $this->contextMatches($context);
        }
    }

    /**
     * Check if a context and all its required contexts match in the current environment
     *
     * @return bool True if context and all its dependencies match
     */
    private function contextMatches(AbstractContext $context): bool
    {
        if (!$context->matches()) {
            return false;
        }

        foreach ($context->getRequiredContexts() as $requiredContextClass) {
            $requiredContext = $this->contexts[$requiredContextClass];
            $contextMatchingResult = $this->contextMatchingResults[$requiredContextClass] ?? null;

            if ($contextMatchingResult === null) {
                $contextMatchingResult = $this->contextMatches($requiredContext);
                $this->contextMatchingResults[$requiredContextClass] = $contextMatchingResult;
            }

            if ($contextMatchingResult === false) {
                return false;
            }
        }

        return true;
    }
}
