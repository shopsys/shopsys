<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\Flag;

use App\Model\Product\Flag\FlagFacade;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class FlagTest extends GraphQlTestCase
{
    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    /**
     * @var \App\Model\Product\Flag\FlagFacade
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
            "name": "' . t('Vyrobeno v DE', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
            "rgbColor": "#ffffff",
            "slug": "' . $this->urlGenerator->generate('front_flag_detail', ['id' => $flag->getId()]) . '",
            "breadcrumb": [
                {
                    "name": "' . t('Vyrobeno v DE', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
                    "slug": "' . $this->urlGenerator->generate('front_flag_detail', ['id' => $flag->getId()]) . '"
                }
            ],
            "products": {
                "edges": [
                    {
                        "node": {
                            "name": "' . t('OLYMPUS VH-620', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        }
                    }
                ]
            },
            "categories": [
                {
                    "name": "' . t('Cameras & Photo', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                }
            ]
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    public function testFlagByUuidFilteredByAnotherFlag(): void
    {
        $flagAction = $this->flagFacade->getById(2);
        $flagNew = $this->flagFacade->getById(3);

        $query = '
            query {
                flag(uuid: "' . $flagAction->getUuid() . '") {
                    products(filter:{flags:["' . $flagNew->getUuid() . '"]}) {
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

        $jsonExpected = '{
            "data": {
                "flag": {
                    "products": {
                        "edges": [
                            {
                                "node": {
                                    "name": "' . t('22\" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                }
                            },
                            {
                                "node": {
                                    "name": "' . t('32\" Philips 32PFL4308', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                }
                            },
                            {
                                "node": {
                                    "name": "' . t('Apple iPhone 5S 64GB, gold', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                }
                            },
                            {
                                "node": {
                                    "name": "' . t('Canon MG3550', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                }
                            },
                            {
                                "node": {
                                    "name": "' . t('Defender 2.0 SPK-480', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                }
                            },
                            {
                                "node": {
                                    "name": "' . t('DeLonghi ECAM 44.660 B Eletta Plus', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                }
                            },
                            {
                                "node": {
                                    "name": "' . t('Genius SP-U150X black-green', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                }
                            },
                            {
                                "node": {
                                    "name": "' . t('Hyundai 32PFL4400', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                }
                            },
                            {
                                "node": {
                                    "name": "' . t('Book 55 best programs for burning CDs and DVDs', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                }
                            },
                            {
                                "node": {
                                    "name": "' . t('Book Computer for Dummies Digital Photography II', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                                }
                            }
                        ]
                    },
                    "categories": [
                        {
                            "name": "' . t('TV, audio', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        },
                        {
                            "name": "' . t('Electronics', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        },
                        {
                            "name": "' . t('Printers', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        },
                        {
                            "name": "' . t('Books', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        },
                        {
                            "name": "' . t('Personal Computers & accessories', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        },
                        {
                            "name": "' . t('Mobile Phones', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        },
                        {
                            "name": "' . t('Food', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        }
                    ]
                }
            }
        }';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
