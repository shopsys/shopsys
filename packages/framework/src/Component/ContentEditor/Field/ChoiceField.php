<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceField extends AbstractField
{
    /**
     * @var array<int, string|int>
     */
    protected array $choices = [];

    /**
     * @param array<int, string|int> $choices
     */
    #[Override]
    public static function create(string $name, array $choices = []): static
    {
        $field = new static($name);
        $field->choices = $choices;

        return $field;
    }

    #[Override]
    public function getFormType(): string
    {
        return ChoiceType::class;
    }

    #[Override]
    public function getFormOptions(): array
    {
        return parent::getFormOptions() + ['choices' => array_combine($this->choices, $this->choices)];
    }

    #[Override]
    public function normalizeValue(mixed $value): mixed
    {
        return in_array($value, $this->choices, true) ? $value : $this->getDefault();
    }
}
