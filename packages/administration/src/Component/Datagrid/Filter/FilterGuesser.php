<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotFilterableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\FrameworkBundle\Model\Product\Product;

/**
 * Picks the filter of a field declared `filterable` by the kind of value its path leads to — a text filter for
 * text, a date filter for a date, a select of the related entities for an association. The filter takes the name
 * and the label of the field.
 *
 * Replace the service in a project to pick differently, typically to offer a filter of the project for a kind of
 * value the platform has none for.
 */
class FilterGuesser
{
    /**
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotFilterableException
     */
    public function guess(FieldDescriptor $field, FilterEnvironment $environment): FilterInterface
    {
        $paths = $field->getSelectProperties();

        if ($paths === []) {
            throw new FieldNotFilterableException($field->getName(), 'a virtual field has no path to filter by.');
        }

        if (count($paths) > 1) {
            throw new FieldNotFilterableException($field->getName(), 'a field combining several properties has no single path to filter by, declare the filter by filters()->add().');
        }

        $path = $paths[0];

        try {
            $pathDescription = $environment->describePath($path);
        } catch (PathNotFoundException $exception) {
            throw new FieldNotFilterableException($field->getName(), $exception->getMessage(), $exception);
        }

        if ($pathDescription === null) {
            throw new FieldNotFilterableException($field->getName(), 'the adapter does not describe its paths, declare the filter by filters()->add().');
        }

        $filter = $this->createFilter($path, $pathDescription);

        if ($filter === null) {
            throw new FieldNotFilterableException($field->getName(), sprintf('no filter is known for a path leading to %s, declare one by filters()->add().', $pathDescription->valueType->name));
        }

        return $filter
            ->setName($field->getFilterName())
            ->setLabel($field->getLabel());
    }

    /**
     * The filter for the kind of value of the path, null when there is none.
     */
    protected function createFilter(string $path, PathDescription $pathDescription): ?AbstractFilter
    {
        return match ($pathDescription->valueType) {
            PathValueTypeEnum::STRING => TextFilter::new($path),
            PathValueTypeEnum::INTEGER, PathValueTypeEnum::DECIMAL => NumericFilter::new($path),
            PathValueTypeEnum::DATE, PathValueTypeEnum::DATETIME => DateFilter::new($path),
            PathValueTypeEnum::BOOLEAN => BooleanFilter::new($path),
            PathValueTypeEnum::ASSOCIATION => $this->leadsToProduct($pathDescription) ? ProductFilter::new($path) : EntityFilter::new($path),
            default => null,
        };
    }

    /**
     * The catalogue is too large for a select, so a product is picked in the product picker.
     */
    protected function leadsToProduct(PathDescription $pathDescription): bool
    {
        return $pathDescription->targetEntityClass !== null && is_a($pathDescription->targetEntityClass, Product::class, true);
    }
}
