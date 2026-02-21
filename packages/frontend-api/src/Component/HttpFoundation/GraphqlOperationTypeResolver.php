<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\HttpFoundation;

use GraphQL\Error\SyntaxError;
use GraphQL\Language\AST\OperationDefinitionNode;
use GraphQL\Language\Parser;

class GraphqlOperationTypeResolver
{
    protected const string QUERY = 'query';

    protected const string OPERATION_NAME = 'operationName';

    /**
     * @param array<string, mixed> $payload
     */
    public function resolveOperationTypeFromPayload(array $payload): ?string
    {
        $query = $payload[static::QUERY] ?? null;

        if (!is_string($query)) {
            return null;
        }

        $operationName = $payload[static::OPERATION_NAME] ?? null;

        if (!is_string($operationName)) {
            $operationName = null;
        }

        return $this->resolveOperationType($query, $operationName);
    }

    protected function resolveOperationType(string $query, ?string $operationName): ?string
    {
        try {
            $parsedQuery = Parser::parse($query);
        } catch (SyntaxError) {
            return null;
        }

        $operationType = null;

        foreach ($parsedQuery->definitions as $definition) {
            if (!$definition instanceof OperationDefinitionNode) {
                continue;
            }

            if ($operationName !== null) {
                if ($definition->name === null || $definition->name->value !== $operationName) {
                    continue;
                }

                return $definition->operation;
            }

            if ($operationType !== null) {
                return null;
            }

            $operationType = $definition->operation;
        }

        return $operationType;
    }
}
