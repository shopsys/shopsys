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
                'name' => t('Shopsys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'link' => 'https://www.shopsys.cz',
                'extendedText' => t('Terms of promotion', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'extendedTextLink' => 'https://www.shopsys.cz',
            ],
            [
                'name' => t('Documentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'link' => 'https://docs.shopsys.com',
                'extendedText' => t('Terms of promotion', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'extendedTextLink' => 'https://www.shopsys.cz',
            ],
            [
                'name' => t('Become one of us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'link' => 'https://jobs.shopsys.cz',
                'extendedText' => t('Terms of promotion', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'extendedTextLink' => 'https://www.shopsys.cz',
            ],
        ];

        foreach ($responseData as $sliderItem) {
            $this->assertArrayHasKey('uuid', $sliderItem);
            $this->assertTrue(Uuid::isValid($sliderItem['uuid']));

            $this->assertKeysAreSameAsExpected(
                [
                    'name',
                    'link',
                    'extendedText',
                    'extendedTextLink',
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
                'name' => t('Shopsys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'images' => [
                    [
                        'url' => $this->firstDomainUrl . '/content-test/images/sliderItem/web/59.jpg',
                        'name' => 'Slider item 1 image',
                    ],
                ],
            ],
            [
                'name' => t('Documentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'images' => [
                    [
                        'url' => $this->firstDomainUrl . '/content-test/images/sliderItem/web/60.jpg',
                        'name' => 'Slider item 2 image',
                    ],
                ],
            ],
            [
                'name' => t('Become one of us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'images' => [
                    [
                        'url' => $this->firstDomainUrl . '/content-test/images/sliderItem/web/61.jpg',
                        'name' => 'Slider item 3 image',
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
                'name' => t('Shopsys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'images' => [
                    [
                        'url' => $this->firstDomainUrl . '/content-test/images/sliderItem/mobile/103.jpg',
                        'name' => 'Slider item 1 image',
                    ],
                ],
            ],
            [
                'name' => t('Documentation', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'images' => [
                    [
                        'url' => $this->firstDomainUrl . '/content-test/images/sliderItem/mobile/104.jpg',
                        'name' => 'Slider item 2 image',
                    ],
                ],
            ],
            [
                'name' => t('Become one of us', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'images' => [
                    [
                        'url' => $this->firstDomainUrl . '/content-test/images/sliderItem/mobile/105.jpg',
                        'name' => 'Slider item 3 image',
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

    /**
     * @param array $keys
     * @param array $actual
     * @param array $expected
     */
    private function assertKeysAreSameAsExpected(array $keys, array $actual, array $expected): void
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $actual);
            $this->assertSame($expected[$key], $actual[$key]);
        }
    }
}
