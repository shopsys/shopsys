<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Field;

use Override;

/**
 * The value is a list of rows of HTML cells. The field cannot know the table size, so the block fits the grid
 * to its rows and columns. The cells are sanitized by ContentDocumentNormalizer, like any other WYSIWYG value.
 */
class TableCellsField extends AbstractField
{
    #[Override]
    protected mixed $default = [];

    // The framework must not depend on the administration bundle, so the type is referenced by name only.
    #[Override]
    public function getFormType(): string
    {
        return 'Shopsys\AdministrationBundle\Form\Admin\Type\TableCellsType';
    }

    /**
     * @return string[][]
     */
    #[Override]
    public function normalizeValue(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $rows = [];

        foreach ($value as $row) {
            $rows[] = $this->normalizeRow($row);
        }

        return $rows;
    }

    #[Override]
    public function extractText(mixed $value): string
    {
        $texts = [];

        foreach ($this->normalizeValue($value) as $row) {
            array_push($texts, ...$row);
        }

        return implode("\n", array_filter($texts, static fn (string $text): bool => $text !== ''));
    }

    /**
     * @return string[]
     */
    protected function normalizeRow(mixed $row): array
    {
        if (!is_array($row)) {
            return [];
        }

        $cells = [];

        foreach ($row as $cell) {
            $cells[] = is_scalar($cell) ? (string)$cell : '';
        }

        return $cells;
    }
}
