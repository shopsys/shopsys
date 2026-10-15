<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;

/**
 * @phpstan-consistent-constructor
 */
abstract class AbstractField implements FieldInterface
{
    protected ?string $label = null;

    protected ?string $help = null;

    protected mixed $default = null;

    protected bool $required = false;

    /**
     * @var \Symfony\Component\Validator\Constraint[]
     */
    protected array $constraints = [];

    protected function __construct(
        protected readonly string $name,
    ) {
    }

    public static function create(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function help(string $help): static
    {
        $this->help = $help;

        return $this;
    }

    public function default(mixed $default): static
    {
        $this->default = $default;

        return $this;
    }

    public function required(): static
    {
        $this->required = true;

        return $this;
    }

    /**
     * @param \Symfony\Component\Validator\Constraint[] $constraints
     */
    public function constraints(array $constraints): static
    {
        $this->constraints = $constraints;

        return $this;
    }

    #[Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[Override]
    public function getLabel(): string
    {
        return $this->label ?? $this->name;
    }

    #[Override]
    public function getHelp(): ?string
    {
        return $this->help;
    }

    #[Override]
    public function getDefault(): mixed
    {
        return $this->default;
    }

    #[Override]
    public function getConstraints(): array
    {
        return $this->constraints;
    }

    #[Override]
    public function isRequired(): bool
    {
        return $this->required;
    }

    #[Override]
    public function getFormOptions(): array
    {
        return [
            'label' => $this->getLabel(),
            'help' => $this->getHelp(),
            'required' => $this->isRequired(),
            'constraints' => $this->getConstraints(),
        ];
    }

    #[Override]
    public function extractText(mixed $value): string
    {
        return '';
    }
}
