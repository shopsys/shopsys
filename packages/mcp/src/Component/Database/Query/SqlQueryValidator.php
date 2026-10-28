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
    public const string ERROR_UNSUPPORTED_CONSTRUCT = 'The SQL query uses an unsupported construct. Use plain SELECT with joins, subqueries, CTEs, CASE, operators, and allowed functions and casts.';
    public const string ERROR_TABLE_NOT_EXPOSED = 'The SQL query references a table that is not exposed through MCP.';
    public const string ERROR_TYPE_NOT_ALLOWED = 'The SQL query uses a type that is not allowed.';
    public const string ERROR_RECURSIVE_CTE_NOT_SUPPORTED = 'Recursive common table expressions are not supported.';
    public const string ERROR_SCHEMA_QUALIFIED_NAME = 'The SQL query must not qualify names with a schema or database. Use unqualified table names.';
    public const string ERROR_LIMIT_REQUIRED_FORMAT = 'The SQL query must include a top-level LIMIT <= %d.';

    protected const string NODE_TAG_A_CONST = 'A_Const';
    protected const string NODE_TAG_A_EXPR = 'A_Expr';
    protected const string NODE_TAG_A_INDICES = 'A_Indices';
    protected const string NODE_TAG_A_INDIRECTION = 'A_Indirection';
    protected const string NODE_TAG_A_STAR = 'A_Star';
    protected const string NODE_TAG_COLUMN_REF = 'ColumnRef';
    protected const string NODE_TAG_COMMON_TABLE_EXPR = 'CommonTableExpr';
    protected const string NODE_TAG_FUNC_CALL = 'FuncCall';
    protected const string NODE_TAG_RANGE_VAR = 'RangeVar';
    protected const string NODE_TAG_SELECT_STMT = 'SelectStmt';
    protected const string NODE_TAG_SQL_VALUE_FUNCTION = 'SQLValueFunction';
    protected const string NODE_TAG_STRING = 'String';

    /**
     * Whitelist for the PostgreSQL parse tree nodes a query may consist of. Anything else, including any syntax
     * added in future PostgreSQL versions, is rejected, so that no construct can call a function or reach an object
     * the other checks do not know about. Some of the nodes are checked further, see validateWrappedNode().
     */
    protected const array ALLOWED_NODE_TAGS = [
        self::NODE_TAG_SELECT_STMT,
        'ResTarget',
        self::NODE_TAG_RANGE_VAR,
        'RangeSubselect',
        'JoinExpr',
        self::NODE_TAG_COMMON_TABLE_EXPR,
        'SortBy',
        'WindowDef',
        'GroupingSet',
        'List',
        self::NODE_TAG_COLUMN_REF,
        self::NODE_TAG_A_STAR,
        self::NODE_TAG_A_CONST,
        self::NODE_TAG_STRING,
        'Integer',
        'Float',
        'Boolean',
        'A_ArrayExpr',
        'RowExpr',
        self::NODE_TAG_A_INDICES,
        self::NODE_TAG_A_INDIRECTION,
        self::NODE_TAG_A_EXPR,
        'BoolExpr',
        'NullTest',
        'BooleanTest',
        'SubLink',
        'CaseExpr',
        'CaseWhen',
        'CoalesceExpr',
        'MinMaxExpr',
        'CollateClause',
        'GroupingFunc',
        'TypeCast',
        self::NODE_TAG_FUNC_CALL,
        self::NODE_TAG_SQL_VALUE_FUNCTION,
    ];

    /**
     * Whitelist for regular PostgreSQL function calls parsed as FuncCall,
     * for example round(...), count(...), coalesce(...).
     */
    protected const array ALLOWED_REGULAR_FUNCTION_NAMES = [
        'abs',
        'age',
        'array_agg',
        'array_length',
        'array_to_string',
        'avg',
        'bool_and',
        'bool_or',
        'btrim',
        'cardinality',
        'ceil',
        'ceiling',
        'char_length',
        'coalesce',
        'concat',
        'concat_ws',
        'corr',
        'count',
        'cume_dist',
        'date',
        'date_bin',
        'date_part',
        'date_trunc',
        'dense_rank',
        'every',
        'extract',
        'first_value',
        'floor',
        'greatest',
        'initcap',
        'json_agg',
        'json_build_object',
        'jsonb_agg',
        'jsonb_array_length',
        'jsonb_build_object',
        'jsonb_extract_path_text',
        'jsonb_typeof',
        'lag',
        'last_value',
        'lead',
        'least',
        'left',
        'length',
        'lower',
        'ltrim',
        'make_date',
        'max',
        'min',
        'mod',
        'mode',
        'now',
        'nth_value',
        'ntile',
        'nullif',
        'overlaps',
        'percent_rank',
        'percentile_cont',
        'percentile_disc',
        'position',
        'power',
        'rank',
        'regexp_replace',
        'replace',
        'reverse',
        'right',
        'round',
        'row_number',
        'rtrim',
        'sign',
        'split_part',
        'sqrt',
        'starts_with',
        'stddev',
        'stddev_pop',
        'stddev_samp',
        'string_agg',
        'string_to_array',
        'strpos',
        'substring',
        'sum',
        'timezone',
        'to_char',
        'to_date',
        'to_timestamp',
        'translate',
        'trim',
        'trunc',
        'upper',
        'var_pop',
        'var_samp',
        'variance',
        'width_bucket',
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
     * Whitelist for PostgreSQL type names used anywhere in a query,
     * for example in type casts ::text, ::int4, ::numeric, ::timestamp.
     */
    protected const array ALLOWED_TYPE_NAMES = [
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
     * - any schema or database qualification of a table, column, operator or collation
     * - an unqualified pg_* relation, because pg_catalog is searched implicitly before the search_path; the allowed relation
     *   names checked later would not catch it when a query names its CTE pg_* and references the name outside of the CTE scope;
     *   it relies on PostgreSQL naming all relations in pg_catalog with the pg_ prefix
     *
     * @param array<mixed> $node
     */
    protected function findOutsideViewsSchemaReference(array $node): ?string
    {
        if ($this->isSchemaQualifiedName($node)) {
            return self::ERROR_SCHEMA_QUALIFIED_NAME;
        }

        if ($this->getWrappedNodeTag($node) === self::NODE_TAG_RANGE_VAR && str_starts_with((string)($node[self::NODE_TAG_RANGE_VAR]['relname'] ?? ''), 'pg_')) {
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
     */
    protected function isSchemaQualifiedName(array $node): bool
    {
        if (($node['schemaname'] ?? '') !== '' || ($node['catalogname'] ?? '') !== '') {
            return true;
        }

        $wrappedNodeTag = $this->getWrappedNodeTag($node);

        if ($wrappedNodeTag === self::NODE_TAG_COLUMN_REF && count($node[self::NODE_TAG_COLUMN_REF]['fields'] ?? []) > 2) {
            return true;
        }

        if ($wrappedNodeTag === self::NODE_TAG_A_EXPR && count($node[self::NODE_TAG_A_EXPR]['name'] ?? []) > 1) {
            return true;
        }

        foreach (['operName', 'useOp', 'collname'] as $qualifiableNameFieldName) {
            if (is_array($node[$qualifiableNameFieldName] ?? null) && count($node[$qualifiableNameFieldName]) > 1) {
                return true;
            }
        }

        return false;
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

        if (array_key_exists('typeName', $node) && !$this->isAllowedTypeName($node['typeName'])) {
            return self::ERROR_TYPE_NOT_ALLOWED;
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
        if ($this->isParseTreeNodeTag($wrappedNodeTag) && !in_array($wrappedNodeTag, self::ALLOWED_NODE_TAGS, true)) {
            return self::ERROR_UNSUPPORTED_CONSTRUCT;
        }

        if ($wrappedNodeTag === self::NODE_TAG_A_INDIRECTION && !$this->isAllowedIndirection($wrappedNode)) {
            return self::ERROR_UNSUPPORTED_CONSTRUCT;
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
     * PostgreSQL reads (expression).name as a call of the function name, so only subscripts and (expression).* are allowed.
     * alias.name is read the same way when the relation has no such column, it cannot be told apart from a column
     * without resolving the names, but it reaches only the built-in functions taking a whole row.
     *
     * @param array<string, mixed> $indirection
     */
    protected function isAllowedIndirection(array $indirection): bool
    {
        foreach ($indirection['indirection'] ?? [] as $indirectionItem) {
            $indirectionItemTag = is_array($indirectionItem) ? $this->getWrappedNodeTag($indirectionItem) : null;

            if ($indirectionItemTag !== self::NODE_TAG_A_INDICES && $indirectionItemTag !== self::NODE_TAG_A_STAR) {
                return false;
            }
        }

        return true;
    }

    /**
     * The parser turns SQL standard syntax, for example EXTRACT(... FROM ...), TRIM(...) or ... AT TIME ZONE ...,
     * into calls of pg_catalog functions, a call written by the client with a schema is not marked like that.
     *
     * @param array<string, mixed> $funcCall
     */
    protected function isAllowedFunctionCall(array $funcCall): bool
    {
        $functionNameParts = $this->getFunctionNameParts($funcCall);

        if ($functionNameParts === null) {
            return false;
        }

        if (
            count($functionNameParts) === 2
            && $functionNameParts[0] === 'pg_catalog'
            && ($funcCall['funcformat'] ?? null) === 'COERCE_SQL_SYNTAX'
        ) {
            array_shift($functionNameParts);
        }

        return count($functionNameParts) === 1
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

    protected function isAllowedTypeName(mixed $typeName): bool
    {
        $typeNameParts = $this->getTypeNameParts($typeName);

        if ($typeNameParts === null || $typeNameParts === []) {
            return false;
        }

        if (count($typeNameParts) === 1) {
            return in_array($typeNameParts[0], self::ALLOWED_TYPE_NAMES, true);
        }

        return count($typeNameParts) === 2
            && $typeNameParts[0] === 'pg_catalog'
            && in_array($typeNameParts[1], self::ALLOWED_TYPE_NAMES, true);
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
     * Parse tree node tags start with an upper case letter, the typed fields of the nodes with a lower case one.
     */
    protected function isParseTreeNodeTag(string $wrappedNodeTag): bool
    {
        return ctype_upper($wrappedNodeTag[0]);
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
