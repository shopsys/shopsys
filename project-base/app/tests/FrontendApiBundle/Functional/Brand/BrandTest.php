<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Brand;

use App\DataFixtures\Demo\BrandDataFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class BrandTest extends GraphQlTestCase
{
    protected Brand $brand;

    /**
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brand = $this->getReference(BrandDataFixture::BRAND_CANON);
    }

    public function testBrandByUuid(): void
    {
        $query = '
            query {
                brand(uuid: "' . $this->brand->getUuid() . '") {
                    name
                    slug
                    description
                    link
                    seoTitle
                    seoMetaDescription
                    seoH1
                    products (first: 5) {
                        orderingMode
                        edges {
                            node {
                                name
                            }
                        }
                    }
                    images {
                        position
                        type
                        sizes {
                            url
                            size
                            width
                            height
                        }
                    }
                    breadcrumb {
                        name
                        slug
                    }
                }
            }
        ';

        $jsonExpected = '{
    "data": {
        "brand": {
            "name": "' . t('Canon', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
            "slug": "/canon",
            "description": "' . t(
            'This is description of brand %brandName%.',
            ['%brandName%' => 'Canon'],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->getFirstDomainLocale(),
        ) . '",
            "link": "' . $this->getFullUrlPath('/canon') . '",
            "seoTitle": "' . t('%brandName% SEO Title', ['%brandName%' => 'Canon'], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
            "seoMetaDescription": "' . t(
            'This is SEO meta description of brand %brandName%.',
            ['%brandName%' => 'Canon'],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->getFirstDomainLocale(),
        ) . '",
            "seoH1": "' . t('%brandName% SEO H1', ['%brandName%' => 'Canon'], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
            "products": {
                "orderingMode": "PRIORITY",
                "edges": [
                    {
                        "node": {
                            "name": "' . t('Canon EH-22L', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                        }
                    },
                    {
                        "node": {
                            "name": "' . t('Canon EH-22M', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                        }
                    },
                    {
                        "node": {
                            "name": "' . t('Canon EOS 700D', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                        }
                    },
                    {
                        "node": {
                            "name": "' . t('Canon EOS 700E', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                        }
                    },
                    {
                        "node": {
                            "name": "' . t('Canon MG3550', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '"
                        }
                    }
                ]
            },
            "images": [
                {
                    "position": null,
                    "type": null,
                    "sizes": [
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/brand/default/canon_80.jpg') . '",
                            "size": "default",
                            "width": 300,
                            "height": 200
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/brand/original/canon_80.jpg') . '",
                            "size": "original",
                            "width": null,
                            "height": null
                        }
                    ]
                }
            ],
            "breadcrumb": [
                {
                    "name": "' . t('Brand overview', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                    "slug": "' . $this->urlGenerator->generate('front_brand_list') . '"
                },
                {
                  "name": "Canon",
                  "slug": "' . $this->urlGenerator->generate('front_brand_detail', ['id' => $this->brand->getId()]) . '"
                }
            ]
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
