<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Brand;

use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BrandTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    protected Brand $brand;

    protected function setUp(): void
    {
        $brandFacade = self::getContainer()->get(BrandFacade::class);
        $this->brand = $brandFacade->getById(2);

        parent::setUp();
    }

    public function testBrandByUuid(): void
    {
        $query = '
            query {
                brand(uuid: "' . $this->brand->getUuid() . '") {
                    name
                    description
                    link
                    seoTitle
                    seoMetaDescription
                    seoH1
                    products (first: 5) {
                        edges {
                            node {
                                name
                            }
                        }
                    }
                    images {
                        url,
                        type,
                        size,
                        width,
                        height,
                        position
                    }
                }
            }
        ';

        $jsonExpected = '{
    "data": {
        "brand": {
            "name": "' . t('Canon', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
            "description": "' . t(
            'This is description of brand Canon.',
            [],
            'dataFixtures',
            $this->getFirstDomainLocale()
        ) . '",
            "link": "' . $this->getFullUrlPath('/canon/') . '",
            "seoTitle": "' . t('Canon SEO Title', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
            "seoMetaDescription": "' . t(
            'This is SEO meta description of brand Canon.',
            [],
            'dataFixtures',
            $this->getFirstDomainLocale()
        ) . '",
            "seoH1": "' . t('Canon SEO H1', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
            "products": {
                "edges": [
                    {
                        "node": {
                            "name": "' . t('Canon EH-22L', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        }
                    },
                    {
                        "node": {
                            "name": "' . t('Canon EH-22M', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        }
                    },
                    {
                        "node": {
                            "name": "' . t('Canon EOS 700D', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        }
                    },
                    {
                        "node": {
                            "name": "' . t('Canon EOS 700E', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        }
                    },
                    {
                        "node": {
                            "name": "' . t('Canon MG3550', [], 'dataFixtures', $this->getFirstDomainLocale()) . '"
                        }
                    }
                ]
            },
            "images": [
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/brand/default/80.jpg') . '",
                    "type": null,
                    "size": "default",
                    "width": 300,
                    "height": 200,
                    "position": null
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/brand/original/80.jpg') . '",
                    "type": null,
                    "size": "original",
                    "width": null,
                    "height": null,
                    "position": null
                }
            ]
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
