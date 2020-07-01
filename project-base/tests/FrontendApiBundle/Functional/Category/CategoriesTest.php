<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CategoriesTest extends GraphQlTestCase
{
    public function testRootCategories(): void
    {
        $query = '
            query {
                categories {
                    name
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'categories' => [
                    ['name' => 'Electronics'],
                    ['name' => 'Books'],
                    ['name' => 'Toys'],
                    ['name' => 'Garden tools'],
                    ['name' => 'Food'],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testChildCategories(): void
    {
        $query = '
            query {
                categories {
                    name
                    children {
                        name
                    }
                }
            }
        ';

        $expected = [
            'name' => 'Electronics',
            'children' => [
                ['name' => 'TV, audio'],
                ['name' => 'Cameras & Photo'],
                ['name' => 'Printers'],
                ['name' => 'Personal Computers & accessories'],
                ['name' => 'Mobile Phones'],
                ['name' => 'Coffee Machines'],
            ],
        ];

        $graphQlType = 'categories';
        $response = $this->getResponseContentForQuery($query);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $this->assertArrayHasKey(0, $responseData);
        $this->assertEquals($expected, $responseData[0]);
    }
}
