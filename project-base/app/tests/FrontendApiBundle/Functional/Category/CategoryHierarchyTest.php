<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use App\DataFixtures\Demo\CategoryDataFixture;
use App\Model\Category\Category;
use Override;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class CategoryHierarchyTest extends GraphQlTestCase
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

    /**
     * @inject
     */
    private TransformStringHelper $transformStringHelper;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryTvAudio = $this->getReference(CategoryDataFixture::CATEGORY_TV, Category::class);
        $this->categoryElectronics = $this->getReference(CategoryDataFixture::CATEGORY_ELECTRONICS, Category::class);
        $this->categoryPhoto = $this->getReference(CategoryDataFixture::CATEGORY_PHOTO, Category::class);
        $this->categoryPrinters = $this->getReference(CategoryDataFixture::CATEGORY_PRINTERS, Category::class);
        $this->categoryPc = $this->getReference(CategoryDataFixture::CATEGORY_PC, Category::class);
        $this->categoryPhones = $this->getReference(CategoryDataFixture::CATEGORY_PHONES, Category::class);
        $this->categoryCoffee = $this->getReference(CategoryDataFixture::CATEGORY_COFFEE, Category::class);
        $this->categoryFood = $this->getReference(CategoryDataFixture::CATEGORY_FOOD, Category::class);
        $this->categoryGardenTools = $this->getReference(CategoryDataFixture::CATEGORY_GARDEN_TOOLS, Category::class);
        $this->categoryToys = $this->getReference(CategoryDataFixture::CATEGORY_TOYS, Category::class);
        $this->categoryBooks = $this->getReference(CategoryDataFixture::CATEGORY_BOOKS, Category::class);
    }

    public function testCategoryHierarchyOnSingleCategory(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $printersSlug = $this->transformStringHelper->stringToFriendlyUrlSlug($this->categoryPrinters->getName($firstDomainLocale));

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
            [
                'children' => $this->getExpectedCategoryChildren($this->categoryBooks, [
                    CategoryDataFixture::CATEGORY_FICTION,
                    CategoryDataFixture::CATEGORY_NON_FICTION,
                    CategoryDataFixture::CATEGORY_CHILDRENS_BOOKS,
                ]),
            ],
            [
                'children' => $this->getExpectedCategoryChildren($this->categoryToys, [
                    CategoryDataFixture::CATEGORY_BUILDING_SETS,
                    CategoryDataFixture::CATEGORY_BOARD_GAMES,
                    CategoryDataFixture::CATEGORY_OUTDOOR_TOYS,
                ]),
            ],
            [
                'children' => $this->getExpectedCategoryChildren($this->categoryGardenTools, [
                    CategoryDataFixture::CATEGORY_HAND_TOOLS,
                    CategoryDataFixture::CATEGORY_POWER_TOOLS,
                    CategoryDataFixture::CATEGORY_WATERING_SYSTEMS,
                ]),
            ],
            [
                'children' => $this->getExpectedCategoryChildren($this->categoryFood, [
                    CategoryDataFixture::CATEGORY_SNACKS,
                    CategoryDataFixture::CATEGORY_COFFEE_AND_TEA,
                    CategoryDataFixture::CATEGORY_PANTRY_STAPLES,
                ]),
            ],
        ];

        self::assertSame($expected, $data);
    }

    /**
     * @param string[] $childCategoryReferenceNames
     * @return array<int, array{name: string, categoryHierarchy: array<int, array{id: int, name: string, uuid: string}>}>
     */
    private function getExpectedCategoryChildren(Category $parentCategory, array $childCategoryReferenceNames): array
    {
        return array_map(function (string $childCategoryReferenceName) use ($parentCategory) {
            $childCategory = $this->getReference($childCategoryReferenceName, Category::class);

            return [
                'name' => $childCategory->getName($this->getLocaleForFirstDomain()),
                'categoryHierarchy' => $this->getExpectedCategoryHierarchyData([$parentCategory, $childCategory]),
            ];
        }, $childCategoryReferenceNames);
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
