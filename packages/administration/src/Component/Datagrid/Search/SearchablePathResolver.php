<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Search;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\PathDescribingAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotSearchableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotApplicableToPathException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;

/**
 * Finds the paths the quick search looks for the text in — those of the fields declared `searchable` — and refuses
 * a declaration that cannot work before any query exists.
 */
class SearchablePathResolver
{
    public function __construct(
        protected readonly ExpressionOperatorEnum $expressionOperatorEnum,
    ) {
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotSearchableException
     * @return array<string, string> Labels of the searchable fields by the paths searched in, empty when no field is searchable
     */
    public function resolve(AdapterInterface $adapter, array $fields): array
    {
        $labelsByPath = [];

        foreach ($fields as $field) {
            if ($field->isSearchable() === false) {
                continue;
            }

            foreach ($this->getSearchedPaths($adapter, $field) as $path) {
                $labelsByPath[$path] = $field->getLabel();
            }
        }

        return $labelsByPath;
    }

    /**
     * A search over a date or a number fails in the database, so the declaration is refused right away — except that
     * a field combining several properties (a name with the identifier the template links by) is searched in those of
     * them leading to text. An adapter without a schema cannot be asked; every property is then searched and the check
     * happens when the records are searched.
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotSearchableException
     * @return string[]
     */
    protected function getSearchedPaths(AdapterInterface $adapter, FieldDescriptor $field): array
    {
        $paths = $field->getSelectProperties();

        if ($paths === []) {
            throw new FieldNotSearchableException($field->getName(), 'a virtual field has no path to search in.');
        }

        if ($adapter instanceof PathDescribingAdapterInterface === false) {
            return $paths;
        }

        $searchedPaths = [];
        $lastException = null;

        foreach ($paths as $path) {
            try {
                $this->expressionOperatorEnum->assertApplicable(ExpressionOperatorEnum::CONTAINS, $adapter->describePath($path));
                $searchedPaths[] = $path;
            } catch (PathNotFoundException | OperatorNotApplicableToPathException $exception) {
                if ($exception instanceof PathNotFoundException || $field->hasMultipleProperties() === false) {
                    throw new FieldNotSearchableException($field->getName(), $exception->getMessage(), $exception);
                }

                $lastException = $exception;
            }
        }

        if ($searchedPaths === []) {
            throw new FieldNotSearchableException($field->getName(), 'none of its properties leads to text.', $lastException);
        }

        return $searchedPaths;
    }
}
