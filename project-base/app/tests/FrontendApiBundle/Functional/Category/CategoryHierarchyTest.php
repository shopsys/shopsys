<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Category\Category;
use Shopsys\FrameworkBundle\Component\String\TransformString;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CategoryHierarchyTest extends GraphQlTestCase
{
    private const QUERY_FOLDER = __DIR__ . '/../_graphql/query/CategoryHierarchy';

    private Category $categoryTvAudio;

    private Category $categoryElectronics;

    private Category $categoryPhoto;

    private Category $categoryPrinters;

    private Category $categoryPc;

    private Category $categoryPhones;

    private Category $categoryCoffee;

    private Category $categoryFood;

    private Category $categoryGardenTools;

    private Category $categoryToys;

    private Category $categoryBooks;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var \App\Model\Category\Category $categoryTvAudio */
        $categoryTvAudio = $this->getReference(CategoryDataFixture::CATEGORY_TV);
        /** @var \App\Model\Category\Category $categoryElectronics */
        $categoryElectronics = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS);
        /** @var \App\Model\Category\Category $categoryPhoto */
        $categoryPhoto = $this->getReference(CategoryDataFixture::CATEGORY_PHOTO);
        /** @var \App\Model\Category\Category $categoryPrinters */
        $categoryPrinters = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS);
        /** @var \App\Model\Category\Category $categoryPc */
        $categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC);
        /** @var \App\Model\Category\Category $categoryPhones */
        $categoryPhones = $this->getReference(CategoryDataFixture::CATEGORY_PHONES);
        /** @var \App\Model\Category\Category $categoryCoffee */
        $categoryCoffee = $this->getReference(CategoryDataFixture::CATEGORY_COFFEE);
        /** @var \App\Model\Category\Category $categoryFood */
        $categoryFood = $this->getReference(CategoryDataFixture::CATEGORY_FOOD);
        /** @var \App\Model\Category\Category $categoryGardenTools */
        $categoryGardenTools = $this->getReference(CategoryDataFixture::CATEGORY_GARDEN_TOOLS);
        /** @var \App\Model\Category\Category $categoryToys */
        $categoryToys = $this->getReference(CategoryDataFixture::CATEGORY_TOYS);
        /** @var \App\Model\Category\Category $categoryBooks */
        $categoryBooks = $this->getReference(CategoryDataFixture::CATEGORY_BOOKS);

        $this->categoryTvAudio = $categoryTvAudio;
        $this->categoryElectronics = $categoryElectronics;
        $this->categoryPhoto = $categoryPhoto;
        $this->categoryPrinters = $categoryPrinters;
        $this->categoryPc = $categoryPc;
        $this->categoryPhones = $categoryPhones;
        $this->categoryCoffee = $categoryCoffee;
        $this->categoryFood = $categoryFood;
        $this->categoryGardenTools = $categoryGardenTools;
        $this->categoryToys = $categoryToys;
        $this->categoryBooks = $categoryBooks;
    }

    public function testCategoryHierarchyOnSingleCategory(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $printersSlug = TransformString::stringToFriendlyUrlSlug($this->categoryPrinters->getName($firstDomainLocale));

        $response = $this->getResponseContentForGql(self::QUERY_FOLDER . '/SingleCategoryQuery.graphql', [
            'urlSlug' => $printersSlug,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $expected = [
            'categoryHierarchy' => $this->getExpectedCategoryHierarchyData([$this->categoryElectronics, $this->categoryPrinters]),
        ];

        self::assertSame($expected, $data);
    }

    public function testCategoryHierarchyOnCategoryList(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $response = $this->getResponseContentForGql(self::QUERY_FOLDER . '/CategoryListQuery.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'categories');

        $expected = [
            [
                'name' => t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'categoryHierarchy' => $this->getExpectedCategoryHierarchyData([$this->categoryElectronics]),
            ],
            [
                'name' => t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'categoryHierarchy' => $this->getExpectedCategoryHierarchyData([$this->categoryBooks]),
            ],
            [
                'name' => t('Toys', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'categoryHierarchy' => $this->getExpectedCategoryHierarchyData([$this->categoryToys]),
            ],
            [
                'name' => t('Garden tools', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'categoryHierarchy' => $this->getExpectedCategoryHierarchyData([$this->categoryGardenTools]),
            ],
            [
                'name' => t('Food', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                'categoryHierarchy' => $this->getExpectedCategoryHierarchyData([$this->categoryFood]),
            ],
        ];

        self::assertSame($expected, $data);
    }

    public function testCategoryHierarchyOnCategoryChildren(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $response = $this->getResponseContentForGql(self::QUERY_FOLDER . '/ChildrenQuery.graphql');
        $data = $this->getResponseDataForGraphQlType($response, 'categories');

        $expected = [
            [
                'children' => [
                    [
                        'name' => t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'categoryHierarchy' => $this->getExpectedCategoryHierarchyData([$this->categoryElectronics, $this->categoryTvAudio]),
                    ],
                    [
                        'name' => t('Cameras & Photo', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'categoryHierarchy' => $this->getExpectedCategoryHierarchyData([$this->categoryElectronics, $this->categoryPhoto]),
                    ],
                    [
                        'name' => t('Printers', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'categoryHierarchy' => $this->getExpectedCategoryHierarchyData([$this->categoryElectronics, $this->categoryPrinters]),
                    ],
                    [
                        'name' => t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'categoryHierarchy' => $this->getExpectedCategoryHierarchyData([$this->categoryElectronics, $this->categoryPc]),
                    ],
                    [
                        'name' => t('Mobile Phones', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'categoryHierarchy' => $this->getExpectedCategoryHierarchyData([$this->categoryElectronics, $this->categoryPhones]),
                    ],
                    [
                        'name' => t('Coffee Machines', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $firstDomainLocale),
                        'categoryHierarchy' => $this->getExpectedCategoryHierarchyData([$this->categoryElectronics, $this->categoryCoffee]),
                    ],
                ],
            ],
            ['children' => []],
            ['children' => []],
            ['children' => []],
            ['children' => []],
        ];

        self::assertSame($expected, $data);
    }

    /**
     * @param \App\Model\Category\Category[] $categories
     * @return array<int, array{id: int, name: string, uuid: string}>
     */
    private function getExpectedCategoryHierarchyData(array $categories): array
    {
        return array_map(fn (Category $category) => [
            'id' => $category->getId(),
            'name' => $category->getName($this->getLocaleForFirstDomain()),
            'uuid' => $category->getUuid(),
        ], $categories);
    }
}
