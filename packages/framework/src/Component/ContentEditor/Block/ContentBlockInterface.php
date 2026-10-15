<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block;

interface ContentBlockInterface
{
    public function getType(): string;

    /**
     * @return string[]
     */
    public function getProfiles(): array;

    public function getPriority(): int;

    public function getLabel(): string;

    public function getIcon(): ?string;

    public function getCategory(): string;

    /**
     * @return \Shopsys\FrameworkBundle\Component\ContentEditor\Field\FieldInterface[]
     */
    public function getFields(): array;

    /**
     * @return array<string, mixed>
     */
    public function getDefaultData(): array;

    /**
     * Custom form type replacing the form generated from the fields, null for the generated one
     */
    public function getFormType(): ?string;

    /**
     * Makes values of different fields consistent with each other, e.g. fits a grid to its size, after each field is normalized on its own
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function normalizeData(array $data): array;

    /**
     * Converts stored data to the storefront-facing variant, which is never written back to the database
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function export(array $data, ExportContext $context): array;

    /**
     * @param array<string, mixed> $data
     */
    public function extractText(array $data): string;
}
