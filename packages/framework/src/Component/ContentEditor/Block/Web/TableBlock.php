<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ContentEditor\Block\Web;

use Override;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\AbstractContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\Attribute\ContentBlock;
use Shopsys\FrameworkBundle\Component\ContentEditor\Block\ExportContext;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\ChoiceField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\NumberField;
use Shopsys\FrameworkBundle\Component\ContentEditor\Field\TableCellsField;
use Symfony\Component\Validator\Constraints\Range;

#[ContentBlock(type: 'table')]
class TableBlock extends AbstractContentBlock
{
    public const string VARIANT_DEFAULT = 'default';
    public const string VARIANT_SECONDARY = 'secondary';

    public const int DEFAULT_ROWS = 2;
    public const int DEFAULT_COLUMNS = 2;
    public const int MAX_ROWS = 20;
    public const int MAX_COLUMNS = 8;

    #[Override]
    public function getLabel(): string
    {
        return t('Table');
    }

    #[Override]
    public function getIcon(): string
    {
        return 'tabler:table';
    }

    #[Override]
    public function getFields(): array
    {
        return [
            ExtendedClassNameResolver::resolve(NumberField::class)::create('rows')
                ->label(t('Rows'))
                ->default(static::DEFAULT_ROWS)
                ->constraints([new Range(min: 1, max: static::MAX_ROWS)]),
            ExtendedClassNameResolver::resolve(NumberField::class)::create('columns')
                ->label(t('Columns'))
                ->default(static::DEFAULT_COLUMNS)
                ->constraints([new Range(min: 1, max: static::MAX_COLUMNS)]),
            ExtendedClassNameResolver::resolve(ChoiceField::class)::create('variant', [static::VARIANT_DEFAULT, static::VARIANT_SECONDARY])
                ->label(t('Variant'))
                ->default(static::VARIANT_DEFAULT),
            ExtendedClassNameResolver::resolve(TableCellsField::class)::create('cells')
                ->label(t('Cells'))
                ->default($this->createEmptyCells(static::DEFAULT_ROWS, static::DEFAULT_COLUMNS)),
        ];
    }

    // the cells field cannot know the table size, so the storefront gets a grid of exactly rows x columns here
    #[Override]
    public function export(array $data, ExportContext $context): array
    {
        $rows = max(1, min(static::MAX_ROWS, (int)($data['rows'] ?? static::DEFAULT_ROWS)));
        $columns = max(1, min(static::MAX_COLUMNS, (int)($data['columns'] ?? static::DEFAULT_COLUMNS)));
        $cells = $this->createEmptyCells($rows, $columns);

        foreach ($cells as $rowIndex => $row) {
            foreach ($row as $columnIndex => $emptyCell) {
                $cells[$rowIndex][$columnIndex] = $data['cells'][$rowIndex][$columnIndex] ?? $emptyCell;
            }
        }

        $data['rows'] = $rows;
        $data['columns'] = $columns;
        $data['cells'] = $cells;

        return $data;
    }

    /**
     * @return string[][]
     */
    protected function createEmptyCells(int $rows, int $columns): array
    {
        return array_fill(0, $rows, array_fill(0, $columns, ''));
    }
}
