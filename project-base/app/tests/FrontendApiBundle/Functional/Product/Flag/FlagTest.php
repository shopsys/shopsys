<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\Flag;

use App\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Component\ArrayUtils\ArraySorter;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class FlagTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    /**
     * @inject
     */
    protected FlagFacade $flagFacade;

    public function testFlagByUuid(): void
    {
        $flag = $this->flagFacade->getById(6);

        $query = '
            query {
                flag(uuid: "' . $flag->getUuid() . '") {
                    name
                    rgbColor
                    slug
                    breadcrumb {
                        name
                        slug
                    }
                    products {
                        orderingMode
                        edges {
                            node {
                                name
                            }
                        }
                    }
                    categories {
                        name
                    }
                }
            }
        ';

        $jsonExpected = '{
    "data": {
        "flag": {
            "name": "' . t('Made in DE', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
            "rgbColor": "#ffffff",
            "slug": "' . $this->urlGenerator->generate('front_flag_detail', ['id' => $flag->getId()]) . '",
            "breadcrumb": [
                {
                    "name": "' . t('Made in DE', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                    "slug": "' . $this->urlGenerator->generate('front_flag_detail', ['id' => $flag->getId()]) . '"
                }
            ],
            "products": {
                "orderingMode": "PRIORITY",
                "edges": [
                    {
                        "node": {
                            "name": "' . t('OLYMPUS VH-620', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                        }
                    }
                ]
            },
            "categories": [
                {
                    "name": "' . t('Cameras & Photo', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                },
                {
                    "name": "' . t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                }
            ]
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    public function testFlagByUuidFilteredByAnotherFlag(): void
    {
        $limit = 5;
        $flagAction = $this->flagFacade->getById(2);
        $flagNew = $this->flagFacade->getById(3);

        $query = '
            query {
                flag(uuid: "' . $flagAction->getUuid() . '") {
                    products(first:' . $limit . ', filter:{flags:["' . $flagNew->getUuid() . '"]}) {
                        edges {
                            node {
                                name
                            }
                        }
                    }
                    categories(productFilter:{flags:["' . $flagNew->getUuid() . '"]}) {
                        name
                    }
                }
            }
        ';

        $products = [
            [
                'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('32" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('32" Hyundai 32PFL4400', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Apple iPhone 5S 64GB, gold', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Book 55 best programs for burning CDs and DVDs', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Book Computer for Dummies Digital Photography II', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Book of procedures for dealing with traffic accidents', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Canon MG3550', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('Defender 2.0 SPK-480', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
            [
                'name' => t('DeLonghi ECAM 44.660 B Eletta Plus', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
            ],
        ];

        ArraySorter::sortArrayAlphabeticallyByValue('name', $products, $this->getLocaleForFirstDomain());

        $productsWithNodes = [];

        for ($i = 0; $i < $limit; $i++) {
            $productsWithNodes[] = [
                'node' => $products[$i],
            ];
        }

        $arrayExpected = [
            'data' => [
                'flag' => [
                    'products' => [
                        'edges' => $productsWithNodes,
                    ],
                    'categories' => [
                        [
                            'name' => t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                        [
                            'name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                        [
                            'name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                        [
                            'name' => t('Printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                        [
                            'name' => t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                        [
                            'name' => t('Mobile Phones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                        [
                            'name' => t('Food', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedJson($query, json_encode($arrayExpected, JSON_THROW_ON_ERROR));
    }
}
