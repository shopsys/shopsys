<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
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
                    ['name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('Toys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('Garden tools', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                    ['name' => t('Food', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
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
            'name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'children' => [
                ['name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                ['name' => t('Cameras & Photo', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                ['name' => t('Printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                ['name' => t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                ['name' => t('Mobile Phones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                ['name' => t('Coffee Machines', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
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
