<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter;

use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;

/**
 * Computes values of fields the adapter cannot read directly from the row (transformed or combined from multiple properties)
 * and stores them under the field name, so the grid reads them from there
 */
final class DatagridRowProcessor
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields
     */
    public function __construct(
        private readonly array $fields,
    ) {
    }

    /**
     * @param mixed[] $row
     * @param mixed[][] $results all rows of the current page, empty when a single row is processed
     * @return mixed[]
     */
    public function process(array $row, array $results = []): array
    {
        foreach ($this->fields as $field) {
            if ($field->getTransform() === null && !$field->hasMultipleProperties()) {
                // the grid reads the value directly from the property
                continue;
            }

            $row[$field->getName()] = $this->computeFieldValue($field, $row, $results);
        }

        return $row;
    }

    /**
     * @param mixed[] $row
     * @param mixed[][] $results
     */
    private function computeFieldValue(FieldDescriptor $field, array $row, array $results): mixed
    {
        $valuesByProperty = [];

        foreach ($field->getProperties() as $property) {
            $valuesByProperty[$property] = $row[$property] ?? null;
        }

        $value = $field->hasMultipleProperties() ? $valuesByProperty : reset($valuesByProperty);

        if ($field->getTransform() !== null) {
            return call_user_func($field->getTransform(), $value, $row, $results);
        }

        if (is_array($value) && $field->getTemplate() === null) {
            // without a template there is nothing to render the combined values with, so they are joined into one string
            return implode(' ', array_filter($value, static fn (mixed $propertyValue) => $propertyValue !== null && $propertyValue !== ''));
        }

        return $value;
    }
}
