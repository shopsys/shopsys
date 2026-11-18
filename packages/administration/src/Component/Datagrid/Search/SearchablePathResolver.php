<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Search;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\PathDescribingAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\InvalidDatagridDeclarationException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;

/**
 * Finds the paths the quick search looks for the text in — those of the fields declared `searchable`.
 * A declaration that cannot work fails before any query exists.
 */
class SearchablePathResolver
{
    public function __construct(
        protected readonly ExpressionOperatorEnum $expressionOperatorEnum,
    ) {
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields
     * @return list<\Shopsys\AdministrationBundle\Component\Datagrid\Search\SearchedPath> Empty when no field is searchable
     */
    public function resolve(AdapterInterface $adapter, array $fields): array
    {
        $searchedPaths = [];

        foreach ($fields as $field) {
            $searchable = $field->getSearchable();

            if ($searchable === null) {
                continue;
            }

            foreach ($this->getPaths($field, $searchable) as $path) {
                $searchedPaths[] = new SearchedPath(
                    $path,
                    $searchable->label ?? $field->getLabel(),
                    $this->getValueType($adapter, $field, $searchable, $path),
                );
            }
        }

        return $searchedPaths;
    }

    /**
     * Only the properties of the field are searched. The administrator is allowed to see them, while any other
     * column may belong to a field hidden from them.
     *
     * @return string[]
     */
    protected function getPaths(FieldDescriptor $field, Searchable $searchable): array
    {
        $properties = $field->getSelectProperties();
        $paths = $searchable->paths ?? $properties;

        if ($properties === []) {
            throw InvalidDatagridDeclarationException::fieldNotSearchable($field->getName(), 'a virtual field has no path to search in.');
        }

        if ($paths === []) {
            throw InvalidDatagridDeclarationException::fieldNotSearchable($field->getName(), 'the paths of its Searchable are empty.');
        }

        $pathsOutsideField = array_diff($paths, $properties);

        if ($pathsOutsideField !== []) {
            throw InvalidDatagridDeclarationException::fieldNotSearchable($field->getName(), sprintf(
                'only its properties can be searched, "%s" is none of them.',
                implode('", "', $pathsOutsideField),
            ));
        }

        return $paths;
    }

    /**
     * The value type of the path, which decides how the quick search compares it:
     *
     * - text is searched by `contains`;
     * - a UUID is compared by `equals`;
     * - any other value fails, as the database refuses to search text in it;
     * - null for an adapter that does not describe its paths — the path is searched as text and checked only when
     *   the records are searched.
     */
    protected function getValueType(
        AdapterInterface $adapter,
        FieldDescriptor $field,
        Searchable $searchable,
        string $path,
    ): ?PathValueTypeEnum {
        if ($adapter instanceof PathDescribingAdapterInterface === false) {
            return null;
        }

        try {
            $pathDescription = $adapter->describePath($path);
        } catch (PathNotFoundException $exception) {
            throw InvalidDatagridDeclarationException::fieldNotSearchable($field->getName(), $exception->getMessage(), $exception);
        }

        $reason = $this->expressionOperatorEnum->findReasonOfInapplicability(ExpressionOperatorEnum::CONTAINS, $pathDescription);

        if ($reason !== null && $pathDescription->valueType !== PathValueTypeEnum::GUID) {
            $hint = $searchable->paths === null && $field->hasMultipleProperties()
                ? ' Pick the properties leading to text or a UUID by \'searchable\' => new Searchable(paths: [...]).'
                : '';

            throw InvalidDatagridDeclarationException::fieldNotSearchable($field->getName(), sprintf(
                'Operator "%s" cannot be applied to path "%s": %s%s',
                ExpressionOperatorEnum::CONTAINS,
                $path,
                $reason,
                $hint,
            ));
        }

        return $pathDescription->valueType;
    }
}
