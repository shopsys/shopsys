<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Field;

use Closure;
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
 *     property?: string|null
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
        ]);

        $optionsResolver->setAllowedTypes('label', 'string');
        $optionsResolver->setAllowedTypes('visible', 'bool');
        $optionsResolver->setAllowedTypes('sortable', 'bool');
        $optionsResolver->setAllowedTypes('virtual', 'bool');
        $optionsResolver->setAllowedTypes('help', ['string', 'null']);
        $optionsResolver->setAllowedTypes('template', ['string', 'null']);
        $optionsResolver->setAllowedTypes('transform', [Closure::class, 'null']);
        $optionsResolver->setAllowedTypes('property', ['string', 'null']);

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

    public function getSelectProperty(): ?string
    {
        if ($this->isVirtual()) {
            return null;
        }

        return $this->options['property'] ?? $this->getName();
    }

    public function getMappingProperty(): ?string
    {
        if ($this->isVirtual() && $this->options['property'] === null && $this->getTransform() === null) {
            return null;
        }

        return $this->options['property'] ?? $this->getName();
    }
}
