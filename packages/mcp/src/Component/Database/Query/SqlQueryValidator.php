<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Query;

use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\McpBundle\Component\Database\Query\Exception\SqlQueryParsingException;
use Shopsys\McpBundle\Component\Database\Schema\ExposedSchemaProvider;

class SqlQueryValidator
{
    public const string ERROR_EMPTY_QUERY = 'SQL query must not be empty.';
    public const string ERROR_ONLY_SELECT_SUPPORTED = 'Only SELECT statements are supported.';
    public const string ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT = 'The SQL query uses an unsupported read/write construct.';
    public const string ERROR_TABLE_NOT_EXPOSED = 'The SQL query references a table that is not exposed through MCP.';
    public const string ERROR_DISALLOWED_CAST_TARGET = 'The SQL query uses a cast target that is not allowed.';
    public const string ERROR_RECURSIVE_CTE_NOT_SUPPORTED = 'Recursive common table expressions are not supported.';
    public const string ERROR_SCHEMA_QUALIFIED_NAME = 'The SQL query must not qualify names with a schema or database. Use unqualified table names.';
    public const string ERROR_LIMIT_REQUIRED_FORMAT = 'The SQL query must include a top-level LIMIT <= %d.';

    protected const string NODE_TAG_A_CONST = 'A_Const';
    protected const string NODE_TAG_A_EXPR = 'A_Expr';
    protected const string NODE_TAG_COLUMN_REF = 'ColumnRef';
    protected const string NODE_TAG_COMMON_TABLE_EXPR = 'CommonTableExpr';
    protected const string NODE_TAG_DELETE_STMT = 'DeleteStmt';
    protected const string NODE_TAG_FUNC_CALL = 'FuncCall';
    protected const string NODE_TAG_INSERT_STMT = 'InsertStmt';
    protected const string NODE_TAG_INTO_CLAUSE = 'IntoClause';
    protected const string NODE_TAG_LOCKING_CLAUSE = 'LockingClause';
    protected const string NODE_TAG_MERGE_STMT = 'MergeStmt';
    protected const string NODE_TAG_RANGE_FUNCTION = 'RangeFunction';
    protected const string NODE_TAG_RANGE_TABLE_FUNC = 'RangeTableFunc';
    protected const string NODE_TAG_RANGE_VAR = 'RangeVar';
    protected const string NODE_TAG_SELECT_STMT = 'SelectStmt';
    protected const string NODE_TAG_SQL_VALUE_FUNCTION = 'SQLValueFunction';
    protected const string NODE_TAG_STRING = 'String';
    protected const string NODE_TAG_TYPE_CAST = 'TypeCast';
    protected const string NODE_TAG_UPDATE_STMT = 'UpdateStmt';

    protected const array UNSUPPORTED_NODE_TAGS = [
        self::NODE_TAG_DELETE_STMT,
        self::NODE_TAG_INSERT_STMT,
        self::NODE_TAG_INTO_CLAUSE,
        self::NODE_TAG_LOCKING_CLAUSE,
        self::NODE_TAG_MERGE_STMT,
        self::NODE_TAG_RANGE_FUNCTION,
        self::NODE_TAG_RANGE_TABLE_FUNC,
        self::NODE_TAG_UPDATE_STMT,
    ];

    /**
     * Whitelist for regular PostgreSQL function calls parsed as FuncCall,
     * for example round(...), count(...), coalesce(...).
     */
    protected const array ALLOWED_REGULAR_FUNCTION_NAMES = [
        'abs',
        'avg',
        'btrim',
        'ceil',
        'ceiling',
        'char_length',
        'coalesce',
        'concat',
        'count',
        'date_trunc',
        'extract',
        'floor',
        'greatest',
        'least',
        'length',
        'lower',
        'ltrim',
        'max',
        'min',
        'nullif',
        'position',
        'replace',
        'round',
        'rtrim',
        'split_part',
        'string_agg',
        'strpos',
        'substring',
        'sum',
        'to_char',
        'to_date',
        'trim',
        'upper',
    ];

    /**
     * Whitelist for special PostgreSQL SQL value expressions parsed as SQLValueFunction,
     * for example current_date, current_timestamp, localtimestamp.
     */
    protected const array ALLOWED_SPECIAL_SQL_VALUE_OPS = [
        'SVFOP_CURRENT_DATE',
        'SVFOP_CURRENT_TIME',
        'SVFOP_CURRENT_TIME_N',
        'SVFOP_CURRENT_TIMESTAMP',
        'SVFOP_CURRENT_TIMESTAMP_N',
        'SVFOP_LOCALTIME',
        'SVFOP_LOCALTIMESTAMP',
        'SVFOP_LOCALTIMESTAMP_N',
    ];

    /**
     * Whitelist for PostgreSQL type casts parsed as TypeCast,
     * for example ::text, ::int4, ::numeric, ::timestamp.
     */
    protected const array ALLOWED_TYPE_CAST_NAMES = [
        'bool',
        'boolean',
        'bpchar',
        'char',
        'date',
        'float4',
        'float8',
        'int2',
        'int4',
        'int8',
        'interval',
        'json',
        'jsonb',
        'numeric',
        'text',
        'time',
        'timestamp',
        'timestamptz',
        'timetz',
        'uuid',
        'varchar',
        'bytea',
    ];

    public function __construct(
        protected readonly ExposedSchemaProvider $exposedSchemaProvider,
        protected readonly PostgresQueryParser $postgresQueryParser,
        protected readonly int $maxReturnedRows,
    ) {
    }

    /**
     * Which columns a query can read is enforced by the database: the MCP connection resolves unqualified names
     * to views that contain only the exposed columns (see McpDatabaseViewsManager). The validator therefore only
     * keeps the query a single read-only SELECT within the views schema, with allowed functions and casts and a LIMIT.
     */
    public function validate(string $sql): SqlQueryValidationResult
    {
        if (trim($sql) === '') {
            return ExtendedClassNameResolver::resolve(SqlQueryValidationResult::class)::createInvalid(self::ERROR_EMPTY_QUERY);
        }

        try {
            $parsedSqlQuery = $this->postgresQueryParser->parseSingleStatement($sql);
        } catch (SqlQueryParsingException $sqlQueryParsingException) {
            return ExtendedClassNameResolver::resolve(SqlQueryValidationResult::class)::createInvalid($sqlQueryParsingException->getMessage());
        }

        $validationErrorMessage = $this->validateStatement($parsedSqlQuery->statement);

        if ($validationErrorMessage !== null) {
            return ExtendedClassNameResolver::resolve(SqlQueryValidationResult::class)::createInvalid($validationErrorMessage);
        }

        return ExtendedClassNameResolver::resolve(SqlQueryValidationResult::class)::createValid($parsedSqlQuery->singleStatementSql);
    }

    /**
     * @param array<string, mixed> $statement
     */
    protected function validateStatement(array $statement): ?string
    {
        if ($this->getWrappedNodeTag($statement) !== self::NODE_TAG_SELECT_STMT) {
            return self::ERROR_ONLY_SELECT_SUPPORTED;
        }

        $validationErrorMessage = $this->findOutsideViewsSchemaReference($statement);

        if ($validationErrorMessage !== null) {
            return $validationErrorMessage;
        }

        if (!$this->hasAllowedLimit($statement[self::NODE_TAG_SELECT_STMT])) {
            return $this->getLimitRequiredErrorMessage();
        }

        return $this->validateNode(
            $statement,
            array_fill_keys([...$this->exposedSchemaProvider->getExposedTableNames(), ...$this->getCteNames($statement)], true),
        );
    }

    /**
     * The MCP connection resolves unqualified names to the views with exposed columns only (see McpDatabaseViewsManager),
     * so a query can reach anything else only by naming it explicitly. The check therefore walks every node of the tree
     * instead of the known node types, so that no construct can carry such a reference past it:
     * - any schema or database qualification
     * - an unqualified pg_* relation, because pg_catalog is searched implicitly before the search_path; the allowed relation
     *   names checked later would not catch it when a query names its CTE pg_* and references the name outside of the CTE scope
     *
     * @param array<mixed> $node
     */
    protected function findOutsideViewsSchemaReference(array $node): ?string
    {
        if (($node['schemaname'] ?? '') !== '' || ($node['catalogname'] ?? '') !== '') {
            return self::ERROR_SCHEMA_QUALIFIED_NAME;
        }

        $wrappedNodeTag = $this->getWrappedNodeTag($node);

        if ($wrappedNodeTag === self::NODE_TAG_COLUMN_REF && count($node[self::NODE_TAG_COLUMN_REF]['fields'] ?? []) > 2) {
            return self::ERROR_SCHEMA_QUALIFIED_NAME;
        }

        // OPERATOR(schema.op)
        if ($wrappedNodeTag === self::NODE_TAG_A_EXPR && count($node[self::NODE_TAG_A_EXPR]['name'] ?? []) > 1) {
            return self::ERROR_SCHEMA_QUALIFIED_NAME;
        }

        if ($wrappedNodeTag === self::NODE_TAG_RANGE_VAR && str_starts_with((string)($node[self::NODE_TAG_RANGE_VAR]['relname'] ?? ''), 'pg_')) {
            return self::ERROR_TABLE_NOT_EXPOSED;
        }

        foreach ($node as $childNode) {
            if (!is_array($childNode)) {
                continue;
            }

            $validationErrorMessage = $this->findOutsideViewsSchemaReference($childNode);

            if ($validationErrorMessage !== null) {
                return $validationErrorMessage;
            }
        }

        return null;
    }

    /**
     * @param array<mixed> $node
     * @param array<string, bool> $allowedRelationNamesSet
     */
    protected function validateNode(array $node, array $allowedRelationNamesSet): ?string
    {
        if (array_key_exists('intoClause', $node)) {
            return self::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT;
        }

        if ($this->isRecursiveWithClause($node['withClause'] ?? null)) {
            return self::ERROR_RECURSIVE_CTE_NOT_SUPPORTED;
        }

        $wrappedNodeTag = $this->getWrappedNodeTag($node);

        if ($wrappedNodeTag !== null && is_array($node[$wrappedNodeTag])) {
            $validationErrorMessage = $this->validateWrappedNode($wrappedNodeTag, $node[$wrappedNodeTag], $allowedRelationNamesSet);

            if ($validationErrorMessage !== null) {
                return $validationErrorMessage;
            }
        }

        foreach ($node as $childNode) {
            if (!is_array($childNode)) {
                continue;
            }

            $validationErrorMessage = $this->validateNode($childNode, $allowedRelationNamesSet);

            if ($validationErrorMessage !== null) {
                return $validationErrorMessage;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $wrappedNode
     * @param array<string, bool> $allowedRelationNamesSet
     */
    protected function validateWrappedNode(
        string $wrappedNodeTag,
        array $wrappedNode,
        array $allowedRelationNamesSet,
    ): ?string {
        if (in_array($wrappedNodeTag, self::UNSUPPORTED_NODE_TAGS, true)) {
            return self::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT;
        }

        if (
            $wrappedNodeTag === self::NODE_TAG_COMMON_TABLE_EXPR
            && (!is_array($wrappedNode['ctequery'] ?? null) || $this->getWrappedNodeTag($wrappedNode['ctequery']) !== self::NODE_TAG_SELECT_STMT)
        ) {
            return self::ERROR_ONLY_SELECT_SUPPORTED;
        }

        if ($wrappedNodeTag === self::NODE_TAG_FUNC_CALL && !$this->isAllowedFunctionCall($wrappedNode)) {
            return self::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT;
        }

        if ($wrappedNodeTag === self::NODE_TAG_SQL_VALUE_FUNCTION && !$this->isAllowedSqlValueFunction($wrappedNode)) {
            return self::ERROR_UNSUPPORTED_READ_WRITE_CONSTRUCT;
        }

        if ($wrappedNodeTag === self::NODE_TAG_TYPE_CAST && !$this->isAllowedTypeCast($wrappedNode)) {
            return self::ERROR_DISALLOWED_CAST_TARGET;
        }

        if ($wrappedNodeTag === self::NODE_TAG_RANGE_VAR && !array_key_exists((string)($wrappedNode['relname'] ?? ''), $allowedRelationNamesSet)) {
            return self::ERROR_TABLE_NOT_EXPOSED;
        }

        return null;
    }

    /**
     * CTE names are collected from the whole query regardless of their scope, the database rejects a CTE referenced
     * outside of it, so the over-approximation is harmless.
     *
     * @param array<mixed> $node
     * @return list<string>
     */
    protected function getCteNames(array $node): array
    {
        $cteNames = [];

        if ($this->getWrappedNodeTag($node) === self::NODE_TAG_COMMON_TABLE_EXPR) {
            $cteNames[] = (string)($node[self::NODE_TAG_COMMON_TABLE_EXPR]['ctename'] ?? '');
        }

        foreach ($node as $childNode) {
            if (is_array($childNode)) {
                $cteNames = [...$cteNames, ...$this->getCteNames($childNode)];
            }
        }

        return $cteNames;
    }

    /**
     * @param array<string, mixed> $funcCall
     */
    protected function isAllowedFunctionCall(array $funcCall): bool
    {
        $functionNameParts = $this->getFunctionNameParts($funcCall);

        return $functionNameParts !== null
            && count($functionNameParts) === 1
            && in_array($functionNameParts[0], self::ALLOWED_REGULAR_FUNCTION_NAMES, true);
    }

    /**
     * @param array<string, mixed> $sqlValueFunction
     */
    protected function isAllowedSqlValueFunction(array $sqlValueFunction): bool
    {
        $sqlValueFunctionOperator = (string)($sqlValueFunction['op'] ?? '');

        return in_array($sqlValueFunctionOperator, self::ALLOWED_SPECIAL_SQL_VALUE_OPS, true);
    }

    /**
     * @param array<string, mixed> $typeCast
     */
    protected function isAllowedTypeCast(array $typeCast): bool
    {
        $typeNameParts = $this->getTypeNameParts($typeCast['typeName'] ?? null);

        if ($typeNameParts === null || $typeNameParts === []) {
            return false;
        }

        if (count($typeNameParts) === 1) {
            return in_array($typeNameParts[0], self::ALLOWED_TYPE_CAST_NAMES, true);
        }

        return count($typeNameParts) === 2
            && $typeNameParts[0] === 'pg_catalog'
            && in_array($typeNameParts[1], self::ALLOWED_TYPE_CAST_NAMES, true);
    }

    protected function isRecursiveWithClause(mixed $withClause): bool
    {
        if (!is_array($withClause)) {
            return false;
        }

        return ($withClause['recursive'] ?? $withClause['WithClause']['recursive'] ?? false) === true;
    }

    /**
     * @param array<string, mixed> $funcCall
     * @return array<int, string>|null
     */
    protected function getFunctionNameParts(array $funcCall): ?array
    {
        $functionNameParts = [];

        foreach ($funcCall['funcname'] ?? [] as $namePart) {
            if (!is_array($namePart) || $this->getWrappedNodeTag($namePart) !== self::NODE_TAG_STRING) {
                return null;
            }

            $functionNamePart = strtolower((string)($namePart[self::NODE_TAG_STRING]['sval'] ?? ''));

            if ($functionNamePart === '') {
                return null;
            }

            $functionNameParts[] = $functionNamePart;
        }

        return $functionNameParts;
    }

    /**
     * @return array<int, string>|null
     */
    protected function getTypeNameParts(mixed $typeName): ?array
    {
        if (!is_array($typeName)) {
            return null;
        }

        $typeNameParts = [];

        foreach ($typeName['names'] ?? [] as $namePart) {
            if (!is_array($namePart) || $this->getWrappedNodeTag($namePart) !== self::NODE_TAG_STRING) {
                return null;
            }

            $typeNamePart = strtolower((string)($namePart[self::NODE_TAG_STRING]['sval'] ?? ''));

            if ($typeNamePart === '') {
                return null;
            }

            $typeNameParts[] = $typeNamePart;
        }

        return $typeNameParts;
    }

    /**
     * @param array<mixed> $node
     */
    protected function getWrappedNodeTag(array $node): ?string
    {
        if (array_is_list($node) || count($node) !== 1) {
            return null;
        }

        $nodeTag = array_key_first($node);

        return is_string($nodeTag) ? $nodeTag : null;
    }

    /**
     * @param array<string, mixed> $selectStatement
     */
    protected function hasAllowedLimit(array $selectStatement): bool
    {
        $limitCount = $selectStatement['limitCount'] ?? null;

        if (!is_array($limitCount) || $this->getWrappedNodeTag($limitCount) !== self::NODE_TAG_A_CONST) {
            return false;
        }

        $integerValue = $limitCount[self::NODE_TAG_A_CONST]['ival']['ival'] ?? null;

        return is_int($integerValue)
            && $integerValue >= 0
            && $integerValue <= $this->maxReturnedRows;
    }

    protected function getLimitRequiredErrorMessage(): string
    {
        return sprintf(self::ERROR_LIMIT_REQUIRED_FORMAT, $this->maxReturnedRows);
    }
}
