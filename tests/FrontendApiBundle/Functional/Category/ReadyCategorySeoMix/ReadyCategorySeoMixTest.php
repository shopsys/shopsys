<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category\ReadyCategorySeoMix;

use App\DataFixtures\Demo\ReadyCategorySeoDataFixture;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ReadyCategorySeoMixTest extends GraphQlTestCase
{
    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     * @inject
     */
    private UrlGeneratorInterface $urlGenerator;

    public function testGetReadyCategorySeoMixDataBySlug()
    {
        /** @var \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix */
        $readyCategorySeoMix = $this->getReferenceForDomain(ReadyCategorySeoDataFixture::READY_CATEGORY_SEO_ELECTRONICS_WITHOUT_HDMI, 1);
        $query = '
            query slug {
                slug(slug: "elektro-bez-hdmi") {
                    ... on Category {
                        name
                        slug
                        seoH1
                        seoTitle
                        seoMetaDescription
                        bestsellers {
                            name
                        }
                        breadcrumb {
                            name
                            slug
                        }
                        readyCategorySeoMixLinks {
                            name
                            slug
                        }
                        linkedCategories {
                            name
                        }
                    }
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'slug' => [
                    'name' => t('Electronics', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                    'slug' => '/elektro-bez-hdmi',
                    'seoH1' => 'Elektro bez HDMI',
                    'seoTitle' => 'Elektro bez HDMI',
                    'seoMetaDescription' => t(
                        'All kind of electronic devices.',
                        [],
                        'dataFixtures',
                        $this->getLocaleForFirstDomain()
                    ),
                    'bestsellers' => [
                        ['name' => t('47" LG 47LA790V (FHD)', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ['name' => t('32" Philips 32PFL4308', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ['name' => t('A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                    ],
                    'breadcrumb' => [
                        [
                            'name' => t('Electronics', [], 'dataFixtures', $this->getLocaleForFirstDomain()),
                            'slug' => $this->urlGenerator->generate('front_product_list', ['id' => $readyCategorySeoMix->getCategory()->getId()]),
                        ],
                    ],
                    'readyCategorySeoMixLinks' => [
                        ['name' => 'Elektro Akce - od nejlevnějšího - 47 - bez hdmi', 'slug' => 'elektro-akce-od-nejlevnejsiho-47-bez-hdmi'],
                        ['name' => 'Elektro bez HDMI', 'slug' => 'elektro-bez-hdmi'],
                        ['name' => 'Elektro nejprodávanější - A-Z - 27" - bez HDMI', 'slug' => 'elektro-nejprodavanejsi-a-z-27-bez-hdmi'],
                        ['name' => 'Elektro Novinky - TOP - 27" - HDMI', 'slug' => 'elektro-novinky-top-27-hdmi'],
                        ['name' => 'Elektro s HDMI', 'slug' => 'elektro-s-hdmi'],
                    ],
                    'linkedCategories' => [
                        ['name' => t('Food', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                        ['name' => t('Garden tools', [], 'dataFixtures', $this->getLocaleForFirstDomain())],
                    ],
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }
}
