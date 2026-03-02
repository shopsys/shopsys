<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Component\HttpFoundation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrontendApiBundle\Component\HttpFoundation\GraphqlOperationTypeResolver;

class GraphqlOperationTypeResolverTest extends TestCase
{
    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('resolveOperationTypeFromPayloadDataProvider')]
    public function testResolveOperationTypeFromPayload(array $payload, ?string $expectedOperationType): void
    {
        $graphqlOperationTypeResolver = new GraphqlOperationTypeResolver();

        $actualOperationType = $graphqlOperationTypeResolver->resolveOperationTypeFromPayload($payload);

        $this->assertSame($expectedOperationType, $actualOperationType);
    }

    /**
     * @return iterable<string, array{payload: array<string, mixed>, expectedOperationType: string|null}>
     */
    public static function resolveOperationTypeFromPayloadDataProvider(): iterable
    {
        yield 'single query operation' => [
            'payload' => [
                'query' => <<<'GRAPHQL'
                    query {
                        products(first: 1) {
                            edges {
                                node {
                                    uuid
                                }
                            }
                        }
                    }
                    GRAPHQL,
            ],
            'expectedOperationType' => 'query',
        ];

        yield 'single mutation operation' => [
            'payload' => [
                'query' => <<<'GRAPHQL'
                    mutation {
                        RemoveProductList(input: {type: WISHLIST}) {
                            uuid
                        }
                    }
                    GRAPHQL,
            ],
            'expectedOperationType' => 'mutation',
        ];

        yield 'multiple operations with operationName' => [
            'payload' => [
                'query' => <<<'GRAPHQL'
                    query ProductListQuery {
                        productList(input: {type: WISHLIST}) {
                            uuid
                        }
                    }

                    mutation RemoveProductListMutation {
                        RemoveProductList(input: {type: WISHLIST}) {
                            uuid
                        }
                    }
                    GRAPHQL,
                'operationName' => 'RemoveProductListMutation',
            ],
            'expectedOperationType' => 'mutation',
        ];

        yield 'multiple operations without operationName is ambiguous' => [
            'payload' => [
                'query' => <<<'GRAPHQL'
                    query ProductListQuery {
                        productList(input: {type: WISHLIST}) {
                            uuid
                        }
                    }

                    mutation RemoveProductListMutation {
                        RemoveProductList(input: {type: WISHLIST}) {
                            uuid
                        }
                    }
                    GRAPHQL,
            ],
            'expectedOperationType' => null,
        ];

        yield 'operationName not matching any operation' => [
            'payload' => [
                'query' => <<<'GRAPHQL'
                    query ProductListQuery {
                        productList(input: {type: WISHLIST}) {
                            uuid
                        }
                    }
                    GRAPHQL,
                'operationName' => 'UnknownOperation',
            ],
            'expectedOperationType' => null,
        ];

        yield 'non-string operationName is ignored' => [
            'payload' => [
                'query' => <<<'GRAPHQL'
                    mutation RemoveProductListMutation {
                        RemoveProductList(input: {type: WISHLIST}) {
                            uuid
                        }
                    }
                    GRAPHQL,
                'operationName' => 123,
            ],
            'expectedOperationType' => 'mutation',
        ];

        yield 'invalid query syntax' => [
            'payload' => [
                'query' => 'query {',
            ],
            'expectedOperationType' => null,
        ];

        yield 'missing query key in payload' => [
            'payload' => [
                'operationName' => 'ProductListQuery',
            ],
            'expectedOperationType' => null,
        ];

        yield 'non-string query in payload' => [
            'payload' => [
                'query' => ['query ProductListQuery { productList(input: {type: WISHLIST}) { uuid } }'],
            ],
            'expectedOperationType' => null,
        ];
    }
}
