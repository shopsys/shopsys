<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\SliderItem;

use Ramsey\Uuid\Uuid;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GetSliderItemsTest extends GraphQlTestCase
{
    public function testGetSliderItems(): void
    {
        $graphQlType = 'sliderItems';
        $response = $this->getResponseContentForQuery($this->getSliderItemsQuery());
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedSliderItemsData = [
            [
                'name' => t('40% SLEVA NA ÚLOŽNÉ PROSTORY', [], 'dataFixtures', $firstDomainLocale),
                'link' => 'https://www.shopsys.cz',
                'extendedText' => t('Pravidla akce', [], 'dataFixtures', $firstDomainLocale),
                'extendedTextLink' => 'https://www.shopsys.cz',
            ],
            [
                'name' => t('40% SLEVA NA POSTELE, MATRACE A ROŠTY', [], 'dataFixtures', $firstDomainLocale),
                'link' => 'https://shopsys.cz',
                'extendedText' => t('Pravidla akce', [], 'dataFixtures', $firstDomainLocale),
                'extendedTextLink' => 'https://www.shopsys.cz',
            ],
            [
                'name' => t('SLEVA 20% + 21% DPH NAVÍC', [], 'dataFixtures', $firstDomainLocale),
                'link' => 'https://shopsys.cz',
                'extendedText' => t('Pravidla akce', [], 'dataFixtures', $firstDomainLocale),
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
                array_shift($expectedSliderItemsData)
            );
        }
    }

    public function testGetSliderItemsWebImages(): void
    {
        $graphQlType = 'sliderItems';
        $response = $this->getResponseContentForQuery($this->getSliderItemsImageQuery('web'));
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedSliderItemsWebImagesData = [
            [
                'name' => t('40% SLEVA NA ÚLOŽNÉ PROSTORY', [], 'dataFixtures', $firstDomainLocale),
                'images' => [
                    [
                        'sizes' => [
                            [
                                'url' => $this->firstDomainUrl . '/content-test/images/sliderItem/web/original/59.jpg',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => t('40% SLEVA NA POSTELE, MATRACE A ROŠTY', [], 'dataFixtures', $firstDomainLocale),
                'images' => [
                    [
                        'sizes' => [
                            [
                                'url' => $this->firstDomainUrl . '/content-test/images/sliderItem/web/original/60.jpg',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => t('SLEVA 20% + 21% DPH NAVÍC', [], 'dataFixtures', $firstDomainLocale),
                'images' => [
                    [
                        'sizes' => [
                            [
                                'url' => $this->firstDomainUrl . '/content-test/images/sliderItem/web/original/61.jpg',
                            ],
                        ],
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
                array_shift($expectedSliderItemsWebImagesData)
            );
        }
    }

    public function testGetSliderItemsMobileImages(): void
    {
        $graphQlType = 'sliderItems';
        $response = $this->getResponseContentForQuery($this->getSliderItemsImageQuery('mobile'));
        $this->assertResponseContainsArrayOfDataForGraphQlType($response, $graphQlType);
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedSliderItemsWebImagesData = [
            [
                'name' => t('40% SLEVA NA ÚLOŽNÉ PROSTORY', [], 'dataFixtures', $firstDomainLocale),
                'images' => [
                    [
                        'sizes' => [
                            [
                                'url' => $this->firstDomainUrl . '/content-test/images/sliderItem/mobile/original/103.jpg',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => t('40% SLEVA NA POSTELE, MATRACE A ROŠTY', [], 'dataFixtures', $firstDomainLocale),
                'images' => [
                    [
                        'sizes' => [
                            [
                                'url' => $this->firstDomainUrl . '/content-test/images/sliderItem/mobile/original/104.jpg',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => t('SLEVA 20% + 21% DPH NAVÍC', [], 'dataFixtures', $firstDomainLocale),
                'images' => [
                    [
                        'sizes' => [
                            [
                                'url' => $this->firstDomainUrl . '/content-test/images/sliderItem/mobile/original/105.jpg',
                            ],
                        ],
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
                array_shift($expectedSliderItemsWebImagesData)
            );
        }
    }

    /**
     * @return string
     */
    private function getSliderItemsQuery(): string
    {
        return '
            {
                sliderItems {
                    uuid
                    name
                    link
                    extendedText
                    extendedTextLink
                }
            }
        ';
    }

    /**
     * @param string $device
     * @return string
     */
    private function getSliderItemsImageQuery(string $device): string
    {
        return '
            {
                sliderItems {
                    uuid
                    name
                    images (type: "' . $device . '", sizes: ["original"]) {
                        sizes {
                            url
                        }
                    }
                }
            }
        ';
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
