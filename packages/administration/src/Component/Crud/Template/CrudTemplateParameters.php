<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Template;

use Shopsys\AdministrationBundle\Component\Config\ActionType;
use Shopsys\FrameworkBundle\Component\Utils\Presentable;
use Webmozart\Assert\Assert;

/**
 * Variables passed to the template of a CRUD action, together with the context of the rendered action.
 *
 * The context lives here instead of in the signature of `configureTemplateParameters()`, so new context can be added
 * without changing the signature of every overriding method.
 */
final class CrudTemplateParameters
{
    /**
     * @param array<string, mixed> $parameters The base parameters of the action
     * @param \Shopsys\FrameworkBundle\Component\Utils\Presentable|null $entity The displayed entity, null for actions without one (list, create)
     */
    public function __construct(
        private readonly ActionType $actionType,
        private array $parameters,
        private readonly ?Presentable $entity = null,
    ) {
    }

    public function getActionType(): ActionType
    {
        return $this->actionType;
    }

    public function isAction(ActionType ...$actionTypes): bool
    {
        return in_array($this->actionType, $actionTypes, true);
    }

    /**
     * Whether the rendered action displays a record, useful for parameters that depend on the record rather than on a specific action.
     */
    public function hasEntity(): bool
    {
        return $this->entity !== null;
    }

    /**
     * Returns the displayed entity narrowed to the given class, so a missing entity or an entity of a wrong class fails fast.
     *
     * @template T of \Shopsys\FrameworkBundle\Component\Utils\Presentable
     * @param class-string<T> $entityClass
     * @return T
     */
    public function getEntity(string $entityClass): Presentable
    {
        Assert::notNull($this->entity, sprintf('The "%s" action has no entity.', $this->actionType->value));
        Assert::isInstanceOf($this->entity, $entityClass);

        return $this->entity;
    }

    /**
     * @return $this
     */
    public function set(string $name, mixed $value): self
    {
        Assert::keyNotExists($this->parameters, $name, sprintf(
            'Template parameter "%s" of the "%s" action is already set. Choose a different name.',
            $name,
            $this->actionType->value,
        ));

        $this->parameters[$name] = $value;

        return $this;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->parameters);
    }

    public function get(string $name): mixed
    {
        Assert::keyExists($this->parameters, $name, sprintf(
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
}
