<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductVariantTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\ShopBundle\Model\Product\Product
     */
    private $productAsMainVariant;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\Product
     */
    private $productAsVariant;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    protected function setUp(): void
    {
        $this->domain = $this->getContainer()->get(Domain::class);
        $productFacade = $this->getContainer()->get(ProductFacade::class);

        $this->productAsMainVariant = $productFacade->getById(150);
        $this->productAsVariant = $productFacade->getById(75);

        parent::setUp();
    }

    public function testProductMainVariantResultData(): void
    {
        $query = '
            query {
                product(uuid: "' . $this->productAsMainVariant->getUuid() . '") {
                    __typename,
                    name,
                    shortDescription
                    ...on MainVariant {
                      variants {
                        name
                      }
                    }
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'product' => [
                    '__typename' => 'MainVariant',
                    'name' => t('Hyundai 22HD44D', [], 'dataFixtures', $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale()),
                    'shortDescription' => t('Television monitor IPS, 16: 9, 5M: 1, 200cd/m2, 5ms GTG, FullHD 1920x1080, DVB-S2/T2/C, 2x HDMI, USB, SCART, 2 x 5W speakers, energ. Class A', [], 'dataFixtures', $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale()),
                    'variants' => [
                        [
                            'name' => t('Hyundai 22HD44D', [], 'dataFixtures', $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale()),
                        ],
                        [
                            'name' => t('60” Hyundai 22HD44D', [], 'dataFixtures', $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale()),
                        ],
                        [
                            'name' => t('51,5” Hyundai 22HD44D', [], 'dataFixtures', $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale()),
                        ],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testProductVariantResultData(): void
    {
        $query = '
            query {
                product(uuid: "' . $this->productAsVariant->getUuid() . '") {
                    __typename,
                    name,
                    shortDescription
                    ...on Variant {
                      mainVariant {
                        name
                      }
                    }
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'product' => [
                    '__typename' => 'Variant',
                    'name' => t('27” Hyundai T27D590EY', [], 'dataFixtures', $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale()),
                    'shortDescription' => t('TV LED, 100Hz, diagonal 80cm 100Hz, Full HD 1920 x 1080, DVB-T / C, 2x HDMI, USB, CI +, VGA, SCART, speakers 16W, energy. Class A +', [], 'dataFixtures', $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale()),
                    'mainVariant' => [
                        'name' => t('32” Hyundai 32PFL4400', [], 'dataFixtures', $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getLocale()),
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
