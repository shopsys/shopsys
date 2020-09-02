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

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $arrayExpected = [
            'data' => [
                'categories' => [
                    ['name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('Books', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('Toys', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('Garden tools', [], 'dataFixtures', $firstDomainLocale)],
                    ['name' => t('Food', [], 'dataFixtures', $firstDomainLocale)],
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

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expected = [
            'name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale),
            'children' => [
                ['name' => t('TV, audio', [], 'dataFixtures', $firstDomainLocale)],
                ['name' => t('Cameras & Photo', [], 'dataFixtures', $firstDomainLocale)],
                ['name' => t('Printers', [], 'dataFixtures', $firstDomainLocale)],
                ['name' => t('Personal Computers & accessories', [], 'dataFixtures', $firstDomainLocale)],
                ['name' => t('Mobile Phones', [], 'dataFixtures', $firstDomainLocale)],
                ['name' => t('Coffee Machines', [], 'dataFixtures', $firstDomainLocale)],
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
