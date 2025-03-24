<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Category\Category;
use Override;
use Shopsys\FrameworkBundle\Component\ArrayUtils\ArraySorterHelper;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CategoryTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private ArraySorterHelper $arraySorterHelper;

    private Category $category;

    /**
     * @inject
     */
    protected UrlGeneratorInterface $urlGenerator;

    /**
     * @inject
     */
    private TransformStringHelper $transformStringHelper;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->category = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
    }

    public function testCategoryNameByUuid(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryQuery.graphql', [
            'categoryUuid' => $this->category->getUuid(),
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'category');

        $readyCategorySeoMixLinks = [
            [
                'name' => t('Electronics from most expensive', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-od-nejdrazsiho',
            ],
            [
                'name' => t('Electronics in black', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-barva-cerna',
            ],
            [
                'name' => t('Electronics in red', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-barva-cervena',
            ],
            [
                'name' => t('Electronics with LED technology and size 30 inch in sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-led-uhlopricka-30-akce',
            ],
            [
                'name' => t('Electronics without HDMI in sale', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-bez-hdmi-akce',
            ],
            [
                'name' => t('Full HD Electronics with LED technology and USB', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()),
                'slug' => '/elektro-full-hd-led-usb',
            ],
        ];

        $this->arraySorterHelper->sortArrayAlphabeticallyByValue('name', $readyCategorySeoMixLinks, $this->getLocaleForFirstDomain());

        $electronicsName = t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $electronicsSlug = '/' . $this->transformStringHelper->stringToFriendlyUrlSlug($electronicsName);

        $this->assertSame($electronicsName, $responseData['name']);
        $this->assertSame(t('Our electronics include devices used for entertainment (flat screen TVs, DVD players, DVD movies, iPods, video games, remote control cars, etc.), communications (telephones, cell phones, email-capable laptops, etc.) and home office activities (e.g., desktop computers, printers, paper shredders, etc.).', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()), $responseData['description']);
        $this->assertSame($electronicsSlug, $responseData['slug']);
        $this->assertSame(t('Electronic devices', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()), $responseData['seoH1']);
        $this->assertSame(t('Electronic stuff', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()), $responseData['seoTitle']);
        $this->assertSame(t(
            'All kind of electronic devices.',
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->getLocaleForFirstDomain(),
        ), $responseData['seoMetaDescription']);
        $this->assertSame([
            ['name' => t('47" LG 47LA790V (FHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
            ['name' => t('32" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
            ['name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
            ['name' => t('A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain())],
        ], $responseData['bestsellers']);
        $this->assertSame([
            [
                'name' => $electronicsName,
                'slug' => $this->urlGenerator->generate('front_product_list', ['id' => $this->category->getId()]),
            ],
        ], $responseData['breadcrumb']);
        $this->assertSame($readyCategorySeoMixLinks, $responseData['readyCategorySeoMixLinks']);
    }

    public function testCategoryParentAndChildNameByUuid(): void
    {
        $locale = $this->getLocaleForFirstDomain();

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryQuery.graphql', [
            'categoryUuid' => $this->category->getUuid(),
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'category');

        $expectedChildren = [
            ['name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
            ['name' => t('Cameras & Photo', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
            ['name' => t('Printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
            ['name' => t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
            ['name' => t('Mobile Phones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
            ['name' => t('Coffee Machines', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale)],
        ];

        $this->assertSame($expectedChildren, $responseData['children']);
        $this->assertNull($responseData['parent']);
    }

    public function testCategoryProductsByUuid(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CategoryQuery.graphql', [
            'categoryUuid' => $this->category->getUuid(),
            'firstProducts' => 10,
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'category');

        $locale = $this->getLocaleForFirstDomain();

        $expectedProducts = [
            ['node' => [
                'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ]],
            ['node' => [
                'name' => t('32" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ]],
            ['node' => [
                'name' => t(
                    'A4tech mouse X-710BK, OSCAR Game, 2000DPI, black,',
                    [],
                    Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
                    $locale,
                ),
            ]],
            ['node' => [
                'name' => t('47" LG 47LA790V (FHD)', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale),
            ]],
        ];

        $this->assertSame($expectedProducts, $responseData['products']['edges']);
    }
}
