<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Component\GraphQL;

use GraphQL\Language\Parser;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\Validator\DocumentValidator;
use PHPUnit\Framework\TestCase;
use Shopsys\FrontendApiBundle\Component\GraphQL\ConnectionAwareQueryComplexity;

final class ConnectionAwareQueryComplexityTest extends TestCase
{
    public function testConnectionMetadataIsCountedOnce(): void
    {
        $queryComplexityRule = new ConnectionAwareQueryComplexity(10000);

        DocumentValidator::validate(
            $this->createSchema(),
            Parser::parse('
                query {
                    products(first: 28) {
                        totalCount
                        pageInfo {
                            hasNextPage
                        }
                        edges {
                            node {
                                id
                                name
                            }
                        }
                    }
                }
            '),
            [$queryComplexityRule],
        );

        $this->assertSame(87, $queryComplexityRule->getQueryComplexity());
    }

    public function testTypeNameDoesNotIncreaseComplexity(): void
    {
        $queryComplexityRule = new ConnectionAwareQueryComplexity(10000);

        DocumentValidator::validate(
            $this->createSchema(),
            Parser::parse('
                query {
                    products(first: 28) {
                        __typename
                        totalCount
                        pageInfo {
                            __typename
                            hasNextPage
                        }
                        edges {
                            __typename
                            node {
                                __typename
                                id
                                name
                            }
                        }
                    }
                }
            '),
            [$queryComplexityRule],
        );

        $this->assertSame(87, $queryComplexityRule->getQueryComplexity());
    }

    public function testConnectionFragmentSpreadsAreSplitIntoMetadataAndItems(): void
    {
        $queryComplexityRule = new ConnectionAwareQueryComplexity(10000);

        DocumentValidator::validate(
            $this->createSchema(),
            Parser::parse('
                query {
                    products(first: 28) {
                        ...ProductConnectionFragment
                    }
                }

                fragment ProductConnectionFragment on ProductConnection {
                    totalCount
                    edges {
                        node {
                            id
                            name
                        }
                    }
                }
            '),
            [$queryComplexityRule],
        );

        $this->assertSame(85, $queryComplexityRule->getQueryComplexity());
    }

    public function testConnectionNodesAreMultiplied(): void
    {
        $queryComplexityRule = new ConnectionAwareQueryComplexity(10000);

        DocumentValidator::validate(
            $this->createSchema(),
            Parser::parse('
                query {
                    products(first: 28) {
                        totalCount
                        nodes {
                            id
                            name
                        }
                    }
                }
            '),
            [$queryComplexityRule],
        );

        $this->assertSame(85, $queryComplexityRule->getQueryComplexity());
    }

    public function testConnectionEdgeFieldsAreMultiplied(): void
    {
        $queryComplexityRule = new ConnectionAwareQueryComplexity(10000);

        DocumentValidator::validate(
            $this->createSchema(),
            Parser::parse('
                query {
                    products(first: 2) {
                        edges {
                            cursor
                            node {
                                id
                            }
                        }
                    }
                }
            '),
            [$queryComplexityRule],
        );

        $this->assertSame(6, $queryComplexityRule->getQueryComplexity());
    }

    public function testAliasesUnderConnectionNodeRemainMultiplied(): void
    {
        $aliases = implode("\n", array_map(
            static fn (int $index): string => sprintf('nameAlias%d: name', $index),
            range(1, 1200),
        ));

        $errors = DocumentValidator::validate(
            $this->createSchema(),
            Parser::parse(sprintf('
                query {
                    products(first: 1) {
                        edges {
                            node {
                                %s
                            }
                        }
                    }
                }
            ', $aliases)),
            [new ConnectionAwareQueryComplexity(1110)],
        );

        $this->assertCount(1, $errors);
        $this->assertSame('Max query complexity should be 1110 but got 1201.', $errors[0]->getMessage());
    }

    public function testConnectionCustomComplexityFunctionOverridesDefaultConnectionComplexity(): void
    {
        $queryComplexityRule = new ConnectionAwareQueryComplexity(10000);

        DocumentValidator::validate(
            $this->createSchema(static fn (int $childrenComplexity, array $arguments): int => 0),
            Parser::parse('
                query {
                    products(first: 2) {
                        totalCount
                        edges {
                            node {
                                id
                                name
                            }
                        }
                    }
                }
            '),
            [$queryComplexityRule],
        );

        $this->assertSame(0, $queryComplexityRule->getQueryComplexity());
    }

    public function testConnectionUsesDefaultCountWithoutPaginationArguments(): void
    {
        $queryComplexityRule = new ConnectionAwareQueryComplexity(10000);

        DocumentValidator::validate(
            $this->createSchema(),
            Parser::parse('
                query {
                    products {
                        edges {
                            node {
                                id
                            }
                        }
                    }
                }
            '),
            [$queryComplexityRule],
        );

        $this->assertSame(20, $queryComplexityRule->getQueryComplexity());
    }

    /**
     * @param (callable(int, array<string, mixed>): int)|null $connectionComplexity
     */
    protected function createSchema(?callable $connectionComplexity = null): Schema
    {
        $productType = new ObjectType([
            'name' => 'Product',
            'fields' => [
                'id' => Type::id(),
                'name' => Type::string(),
            ],
        ]);

        $pageInfoType = new ObjectType([
            'name' => 'PageInfo',
            'fields' => [
                'hasNextPage' => Type::boolean(),
            ],
        ]);

        $productEdgeType = new ObjectType([
            'name' => 'ProductEdge',
            'fields' => [
                'cursor' => Type::string(),
                'node' => $productType,
            ],
        ]);

        $productConnectionType = new ObjectType([
            'name' => 'ProductConnection',
            'fields' => [
                'totalCount' => Type::int(),
                'pageInfo' => $pageInfoType,
                'edges' => Type::listOf($productEdgeType),
                'nodes' => Type::listOf($productType),
            ],
        ]);

        $productsField = [
            'type' => $productConnectionType,
            'args' => [
                'first' => Type::int(),
                'last' => Type::int(),
            ],
        ];

        if ($connectionComplexity !== null) {
            $productsField['complexity'] = $connectionComplexity;
        }

        return new Schema([
            'query' => new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'products' => $productsField,
                ],
            ]),
        ]);
    }
}
