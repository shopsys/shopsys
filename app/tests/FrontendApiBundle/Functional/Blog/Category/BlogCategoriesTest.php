<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Category;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BlogCategoriesTest extends GraphQlTestCase
{
    public function testRootBlogCategories(): void
    {
        $query = '
            query {
                blogCategories {
                    name
                }
            }
        ';

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $arrayExpected = [
            'data' => [
                'blogCategories' => [
                    ['name' => t('Hlavní stránka blogu - %locale%', ['%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testChildBlogCategories(): void
    {
        $query = '
            query {
                blogCategories {
                    name
                    children {
                        name
                    }
                }
            }
        ';

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expected = [
            'name' => t('Hlavní stránka blogu - %locale%', ['%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale),
            'children' => [
                ['name' => t('První podsekce %locale%', ['%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
                ['name' => t('Druhá podsekce %locale%', ['%locale%' => $firstDomainLocale], 'dataFixtures', $firstDomainLocale)],
            ],
        ];

        $graphQlType = 'blogCategories';
        $response = $this->getResponseContentForQuery($query);

        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $this->assertArrayHasKey(0, $responseData);
        $this->assertEquals($expected, $responseData[0]);
    }
}
