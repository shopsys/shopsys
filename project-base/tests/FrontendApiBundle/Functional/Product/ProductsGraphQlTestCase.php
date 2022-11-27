<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductsGraphQlTestCase extends GraphQlTestCase
{
    /**
     * @param string $query
     * @param string $graphQlType
     * @param mixed[] $products
     * @param bool $found
     */
    protected function assertProducts(string $query, string $graphQlType, array $products, bool $found = true): void
    {
        $response = $this->getResponseContentForQuery($query);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        if ($graphQlType !== 'products') {
            $responseData = $responseData['products'];
        }

        $this->assertArrayHasKey('edges', $responseData);

        $queryResult = [];
        foreach ($responseData['edges'] as $edge) {
            $this->assertArrayHasKey('node', $edge);
            $queryResult[] = $edge['node'];
        }

        if ($found === true) {
            $this->assertEquals($products, $queryResult, json_encode($queryResult));
        } else {
            $this->assertNotEquals($products, $queryResult, json_encode($queryResult));
        }
    }
}
