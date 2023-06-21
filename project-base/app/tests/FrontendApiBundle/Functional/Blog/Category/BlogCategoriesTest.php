<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Blog\Category;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
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
                    ['name' => t('Main blog page - %locale%', ['%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
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
            'name' => t('Main blog page - %locale%', ['%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'children' => [
                ['name' => t('First subsection %locale%', ['%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                ['name' => t('Second subsection %locale%', ['%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
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
