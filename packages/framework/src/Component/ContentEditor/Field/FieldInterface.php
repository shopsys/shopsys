<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

interface FieldInterface
{
    public function getName(): string;

    public function getLabel(): string;

    public function getHelp(): ?string;

    public function getDefault(): mixed;

    /**
     * @return \Symfony\Component\Validator\Constraint[]
     */
    public function getConstraints(): array;

    public function isRequired(): bool;

    /**
     * @return class-string|string
     */
    public function getFormType(): string;

    /**
     * @return array<string, mixed>
     */
    public function getFormOptions(): array;

    /**
     * Coerces a stored or submitted value to the shape of this field, falling back to the default for an invalid one
     */
    public function normalizeValue(mixed $value): mixed;

    public function extractText(mixed $value): string;
}
