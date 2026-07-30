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
                        children {
                            name
                            children {
                                name
                            }
                        }
                    }
                }
            }
        ';

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expected = [
            'name' => t('Main blog page - %locale%', ['%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
            'children' => [
                [
                    'name' => t('First subsection %locale%', ['%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'children' => [
                        [
                            'name' => t('Televisions and displays', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'children' => [
                                ['name' => t('Screen technologies', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale)],
                            ],
                        ],
                        [
                            'name' => t('Audio and headphones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'children' => [],
                        ],
                    ],
                ],
                [
                    'name' => t('Second subsection %locale%', ['%locale%' => $firstDomainLocale], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'children' => [],
                ],
                [
                    'name' => t('Product news', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'children' => [],
                ],
                [
                    'name' => t('Care and maintenance', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'children' => [
                        [
                            'name' => t('Cleaning and upkeep', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                            'children' => [],
                        ],
                    ],
                ],
                [
                    'name' => t('Technology and trends', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    'children' => [],
                ],
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
