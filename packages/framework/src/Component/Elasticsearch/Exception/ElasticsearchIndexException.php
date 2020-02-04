<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch\Exception;

use Exception;

class ElasticsearchIndexException extends Exception
{
    /**
     * @param string $indexName
     * @param string $definitionFilepath
     * @return self
     */
    public static function invalidJsonInDefinitionFile(string $indexName, string $definitionFilepath): self
    {
        return new self(sprintf(
            'Invalid JSON in "%s" definition file "%s"',
            $indexName,
            $definitionFilepath
        ));
    }

    /**
     * @param string $definitionFilepath
     * @return self
     */
    public static function cantReadDefinitionFile(string $definitionFilepath): self
    {
        return new self(sprintf(
            'Can\'t read definition file at path "%s". Please check that file exists and has permissions for reading.',
            $definitionFilepath
        ));
    }

    /**
     * @param string $indexName
     * @param array $error
     * @return self
     */
    public static function createIndexError(string $indexName, array $error): self
    {
        return new self(sprintf(
            'Error when creating index "%s":' . PHP_EOL . '"%s"',
            $indexName,
            json_encode($error)
        ));
    }

    /**
     * @param string $alias
     * @param array $error
     * @return self
     */
    public static function createAliasError(string $alias, array $error): self
    {
        return new self(sprintf(
            'Error when creating alias "%s":' . PHP_EOL . '"%s"',
            $alias,
            json_encode($error)
        ));
    }

    /**
     * @param string $indexName
     * @param array $error
     * @return self
     */
    public static function deleteIndexError(string $indexName, array $error): self
    {
        return new self(sprintf(
            'Error when deleting index "%s":' . PHP_EOL . '"%s"',
            $indexName,
            json_encode($error)
        ));
    }

    /**
     * @param string $indexName
     * @return self
     */
    public static function indexAlreadyExists(string $indexName): self
    {
        return new self(sprintf(
            'Index "%s" already exists',
            $indexName
        ));
    }

    /**
     * @param string $indexName
     * @return self
     */
    public static function noRegisteredIndexFound(string $indexName): self
    {
        return new self(sprintf(
            'There is no index "%s" registered',
            $indexName
        ));
    }

    /**
     * @param string $indexName
     * @param array $errors
     * @return static
     */
    public static function bulkUpdateError(string $indexName, array $errors): self
    {
        return new self(sprintf(
            'One or more items return error when updating "%s":' . PHP_EOL . '"%s"',
            $indexName,
            json_encode($errors)
        ));
    }

    /**
     * @param string $alias
     * @return static
     */
    public static function aliasDoesntExists(string $alias): self
    {
        return new self(sprintf(
            'Can\'t found any index with alias "%s".',
            $alias
        ));
    }

    /**
     * @param string $alias
     * @return static
     */
    public static function noIndexFoundForAlias(string $alias): self
    {
        return new self(sprintf(
            'Can\'t resolve index name, there aren\'t any indexes with alias "%s".',
            $alias
        ));
    }

    /**
     * @param string $alias
     * @param array $indexesFound
     * @return static
     */
    public static function moreThanOneIndexFoundForAlias(string $alias, array $indexesFound): self
    {
        return new self(sprintf(
            'Can\'t resolve index name for alias "%s". More than one index found ("%s").',
            $alias,
            implode('", "', $indexesFound)
        ));
    }
}
