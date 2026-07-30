<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\SliderItem;

use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetSliderItemsTest extends GraphQlTestCase
{
    public function testGetSliderItems(): void
    {
        $graphQlType = 'sliderItems';
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SliderItemsQuery.graphql');
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedSliderItemsData = [
            [
                'name' => t('Shopsys Platform', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'description' => t('Build scalable B2C and B2B stores on an open commerce platform designed for complex projects.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'link' => 'https://www.shopsys.cz',
            ],
            [
                'name' => t('Explore the documentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'description' => t('Find practical guides, architecture concepts, and step-by-step instructions for building with Shopsys Platform.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'link' => 'https://docs.shopsys.com',
            ],
            [
                'name' => t('Join our team', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'description' => t('Help us build ambitious e-commerce projects. Explore open roles and grow with Shopsys.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'link' => 'https://jobs.shopsys.cz',
            ],
        ];

        foreach ($responseData as $sliderItem) {
            $this->assertArrayHasKey('uuid', $sliderItem);
            $this->assertTrue(Uuid::isValid($sliderItem['uuid']));

            $this->assertKeysAreSameAsExpected(
                [
                    'name',
                    'description',
                    'link',
                ],
                $sliderItem,
                array_shift($expectedSliderItemsData),
            );
        }
    }

    public function testGetSliderItemsWebImages(): void
    {
        $graphQlType = 'sliderItems';
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SliderItemsQuery.graphql', [
            'imageType' => 'web',
        ]);
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedSliderItemsWebImagesData = [
            [
                'name' => t('Shopsys Platform', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'images' => [
                    [
                        'url' => $this->currentBaseDomainUrl . '/content-test/images/sliderItem/web/650.jpg',
                        'name' => t('E-commerce order preparation in a modern warehouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                ],
            ],
            [
                'name' => t('Explore the documentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'images' => [
                    [
                        'url' => $this->currentBaseDomainUrl . '/content-test/images/sliderItem/web/651.jpg',
                        'name' => t('Developer working with Shopsys Platform documentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                ],
            ],
            [
                'name' => t('Join our team', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'images' => [
                    [
                        'url' => $this->currentBaseDomainUrl . '/content-test/images/sliderItem/web/652.jpg',
                        'name' => t('Shopsys team collaborating in an office', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                ],
            ],
        ];

        foreach ($responseData as $sliderItem) {
            $this->assertArrayHasKey('uuid', $sliderItem);
            $this->assertTrue(Uuid::isValid($sliderItem['uuid']));

            $this->assertKeysAreSameAsExpected(
                [
                    'name',
                    'images',
                ],
                $sliderItem,
                array_shift($expectedSliderItemsWebImagesData),
            );
        }
    }

    public function testGetSliderItemsMobileImages(): void
    {
        $graphQlType = 'sliderItems';
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SliderItemsQuery.graphql', [
            'imageType' => 'mobile',
        ]);
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedSliderItemsWebImagesData = [
            [
                'name' => t('Shopsys Platform', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'images' => [
                    [
                        'url' => $this->currentBaseDomainUrl . '/content-test/images/sliderItem/mobile/653.jpg',
                        'name' => t('E-commerce order preparation in a modern warehouse', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                ],
            ],
            [
                'name' => t('Explore the documentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'images' => [
                    [
                        'url' => $this->currentBaseDomainUrl . '/content-test/images/sliderItem/mobile/654.jpg',
                        'name' => t('Developer working with Shopsys Platform documentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                ],
            ],
            [
                'name' => t('Join our team', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'images' => [
                    [
                        'url' => $this->currentBaseDomainUrl . '/content-test/images/sliderItem/mobile/655.jpg',
                        'name' => t('Shopsys team collaborating in an office', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                    ],
                ],
            ],
        ];

        foreach ($responseData as $sliderItem) {
            $this->assertArrayHasKey('uuid', $sliderItem);
            $this->assertTrue(Uuid::isValid($sliderItem['uuid']));

            $this->assertKeysAreSameAsExpected(
                [
                    'name',
                    'images',
                ],
                $sliderItem,
                array_shift($expectedSliderItemsWebImagesData),
            );
        }
    }

    private function assertKeysAreSameAsExpected(array $keys, array $actual, array $expected): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertSame($expected[$key], $actual[$key]);
        }
    }
}
