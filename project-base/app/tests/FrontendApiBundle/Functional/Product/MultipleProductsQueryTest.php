<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class MultipleProductsQueryTest extends GraphQlTestCase
{
    public function testMultipleProductsQueriesAtOnce(): void
    {
        $translatedName = t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $slug = TransformString::stringToFriendlyUrlSlug($translatedName);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/multipleProductsQuery.graphql', [
            'urlSlug' => $slug,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $firstDomainLocale = $this->getFirstDomainLocale();

        $expectedData = [
            'name' => $translatedName,
            'products' => [
                'edges' => [
                    [
                        'node' => [
                            'name' => t('Samsung UE75HU7500 (UHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                    [
                        'node' => [
                            'name' => t('LG 47LA790W (FHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
            ],
            'productsStatic' => [
                'edges' => [
                    [
                        'node' => [
                            'name' => t('Samsung UE75HU7500 (UHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($expectedData, $data);
    }
}
