<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Elasticsearch\Exception;

use Exception;

class ElasticsearchIndexException extends Exception
{
    /**
     * @param string $documentName
     * @param string $definitionFilename
     * @return self
     */
    public static function invalidJsonInDefinitionFile(string $documentName, string $definitionFilename): self
    {
        return new self(sprintf(
            'Invalid JSON in %s definition file %s',
            $documentName,
            $definitionFilename
        ));
    }

    /**
     * @param string $definitionFilename
     * @return self
     */
    public static function cantReadDefinitionFile(string $definitionFilename): self
    {
        return new self(sprintf(
            'Can\'t read definition file at path %s. Please check that file exists and has permissions for reading.',
            $definitionFilename
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
            'Error when creating index %s:' . PHP_EOL . '%s',
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
            'Error when creating alias %s:' . PHP_EOL . '%s',
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
            'Error when deleting index %s:' . PHP_EOL . '%s',
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
            'Index %s already exists',
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
}
