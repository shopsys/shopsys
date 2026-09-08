<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Template;

use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Webmozart\Assert\Assert;

/**
 * Variables passed to the template of a CRUD action.
 *
 * The base variables of the action are extended by the CRUD controller and by each of its extensions
 * in `configureTemplateParameters()`. A name can be set only once, so no variable can be silently
 * overwritten by another source — the exception names both the colliding sources.
 */
final class CrudTemplateParameters
{
    /**
     * @var array<string, mixed>
     */
    private array $parameters;

    /**
     * @var array<string, class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController|\Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension>|null> Source class of each parameter, null for the base parameters of the action
     */
    private array $sourceClassByName;

    /**
     * @var class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController|\Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension>|null
     */
    private ?string $currentSourceClass = null;

    /**
     * @param array<string, mixed> $baseParameters
     */
    public function __construct(
        private readonly ActionType $actionType,
        array $baseParameters,
    ) {
        $this->parameters = $baseParameters;
        $this->sourceClassByName = array_fill_keys(array_keys($baseParameters), null);
    }

    /**
     * Sets a variable passed to the template.
     *
     * A name already used by the action itself (`title`, `form`, ...), by the CRUD controller
     * or by another extension throws an exception — choose a different name instead.
     *
     * @return $this
     */
    public function set(string $name, mixed $value): self
    {
        Assert::notNull(
            $this->currentSourceClass,
            'Template parameters can be set only inside configureTemplateParameters() of a CRUD controller or its extension.',
        );

        Assert::keyNotExists($this->sourceClassByName, $name, fn (): string => sprintf(
            'Template parameter "%s" cannot be set by "%s" as it is %s. Choose a different name.',
            $name,
            $this->currentSourceClass,
            $this->describeExistingParameter($name),
        ));

        $this->parameters[$name] = $value;
        $this->sourceClassByName[$name] = $this->currentSourceClass;

        return $this;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->sourceClassByName);
    }

    public function get(string $name): mixed
    {
        Assert::keyExists($this->sourceClassByName, $name, sprintf(
            'Template parameter "%s" of the "%s" action is not set.',
            $name,
            $this->actionType->value,
        ));

        return $this->parameters[$name];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->parameters;
    }

    /**
     * Runs the given `configureTemplateParameters()` call, so the parameters set inside are attributed to their source
     * for the collision messages.
     *
     * @internal Called by the CRUD controller when rendering an action
     * @param class-string<\Shopsys\AdministrationBundle\Controller\AbstractCrudController|\Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension> $sourceClass
     * @param callable(): void $configureTemplateParameters
     */
    public function collectFrom(string $sourceClass, callable $configureTemplateParameters): void
    {
        $this->currentSourceClass = $sourceClass;

        try {
            $configureTemplateParameters();
        } finally {
            $this->currentSourceClass = null;
        }
    }

    private function describeExistingParameter(string $name): string
    {
        $sourceClass = $this->sourceClassByName[$name];

        return $sourceClass === null
            ? sprintf('a base parameter of the "%s" action', $this->actionType->value)
            : sprintf('already set by "%s" for the "%s" action', $sourceClass, $this->actionType->value);
    }
}
