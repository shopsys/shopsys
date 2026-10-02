<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Field;

use Closure;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @phpstan-type FieldOptions array{
 *     label?: string,
 *     visible?: bool,
 *     sortable?: bool,
 *     virtual?: bool,
 *     help?: string|null,
 *     template?: string|null,
 *     transform?: null|\Closure(mixed $value, mixed[] $row, mixed[][] $results): mixed,
 *     property?: string|string[]|null,
 *     role?: string|null,
 *     permission?: \Shopsys\FrameworkBundle\Component\Security\Role\Permission|null,
 *     class?: string|null
 * }
 */
final class FieldDescriptor
{
    /**
     * @var FieldOptions
     */
    private array $options;

    /**
     * @param FieldOptions $options
     */
    public function __construct(
        private readonly string $name,
        array $options = [],
    ) {
        $this->options = $this->resolveOptions($options);
    }

    /**
     * @param FieldOptions $options
     * @return FieldOptions
     */
    private function resolveOptions(array $options): array
    {
        $optionsResolver = new OptionsResolver();
        $optionsResolver->setDefaults([
            'label' => $this->name,
            'visible' => true,
            'sortable' => true,
            'virtual' => false,
            'help' => null,
            'template' => null,
            'transform' => null,
            'property' => null,
            'role' => null,
            'permission' => null,
            'class' => null,
        ]);

        $optionsResolver->setAllowedTypes('label', 'string');
        $optionsResolver->setAllowedTypes('visible', 'bool');
        $optionsResolver->setAllowedTypes('sortable', 'bool');
        $optionsResolver->setAllowedTypes('virtual', 'bool');
        $optionsResolver->setAllowedTypes('help', ['string', 'null']);
        $optionsResolver->setAllowedTypes('template', ['string', 'null']);
        $optionsResolver->setAllowedTypes('transform', [Closure::class, 'null']);
        $optionsResolver->setAllowedTypes('property', ['string', 'string[]', 'null']);
        $optionsResolver->setAllowedValues('property', fn (string|array|null $property) => $property !== []);
        $optionsResolver->setAllowedTypes('role', ['string', 'null']);
        $optionsResolver->setAllowedTypes('permission', [Permission::class, 'null']);
        $optionsResolver->setAllowedTypes('class', ['string', 'null']);

        return $optionsResolver->resolve($options);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->options['label'];
    }

    public function update(array $options): void
    {
        $this->options = $this->resolveOptions(array_merge($this->options, $options));
    }

    public function isVisible(): bool
    {
        return $this->options['visible'];
    }

    public function isSortable(): bool
    {
        // Transformed fields are not sortable because it would be confusing for the user to sort by not displayed values
        if ($this->getTransform() !== null) {
            return false;
        }

        if ($this->getMappingProperty() === null) {
            return false;
        }

        return $this->options['sortable'];
    }

    public function isVirtual(): bool
    {
        return $this->options['virtual'];
    }

    public function getHelp(): ?string
    {
        return $this->options['help'];
    }

    public function getTemplate(): ?string
    {
        return $this->options['template'];
    }

    /**
     * @return null|\Closure(mixed $value, mixed[] $row, mixed[][] $results): mixed
     */
    public function getTransform(): ?Closure
    {
        return $this->options['transform'];
    }

    /**
     * Role constant the administrator needs to see the field, null means the role of the datagrid
     */
    public function getRole(): ?string
    {
        return $this->options['role'];
    }

    /**
     * Permission on the role the administrator needs to see the field, null means VIEW
     */
    public function getPermission(): ?Permission
    {
        return $this->options['permission'];
    }

    /**
     * Field is displayed only to administrators with the configured role or permission
     */
    public function isRestricted(): bool
    {
        return $this->options['role'] !== null || $this->options['permission'] !== null;
    }

    public function getClass(): ?string
    {
        return $this->options['class'];
    }

    /**
     * Field combines values of multiple properties (the "property" option is an array)
     */
    public function hasMultipleProperties(): bool
    {
        return is_array($this->options['property']);
    }

    /**
     * Properties (in dot notation) the field takes its value from, the field name when no property is configured
     *
     * @return string[]
     */
    public function getProperties(): array
    {
        return (array)($this->options['property'] ?? $this->getName());
    }

    /**
     * Properties fetched from the data source, none for virtual fields
     *
     * @return string[]
     */
    public function getSelectProperties(): array
    {
        if ($this->isVirtual()) {
            return [];
        }

        return $this->getProperties();
    }

    /**
     * Row key the grid reads the value of the field from, null when the field has no value at all
     */
    public function getMappingProperty(): ?string
    {
        if ($this->isVirtual() && $this->options['property'] === null && $this->getTransform() === null) {
            return null;
        }

        // Values computed by the adapter (transformed or combined from multiple properties) are stored under the field name
        if ($this->getTransform() !== null || $this->hasMultipleProperties()) {
            return $this->getName();
        }

        return $this->options['property'] ?? $this->getName();
    }
}
