<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Category;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CategoryHierarchyTest extends GraphQlTestCase
{
    private const QUERY_FOLDER = __DIR__ . '/../_graphql/query/CategoryHierarchy';

    public function testCategoryHierarchyOnSingleCategory(): void
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();

        $response = $this->getResponseContentForGql(self::QUERY_FOLDER . '/SingleCategoryQuery.graphql', [
            'urlSlug' => 'tiskarny',
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'category');

        $expected = [
            'categoryHierarchy' => [
                [
                    'name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale),
                    'uuid' => 'dea6764b-a03b-4171-8243-46e730c8b90b',
                ],
                [
                    'name' => t('Printers', [], 'dataFixtures', $firstDomainLocale),
                    'uuid' => 'a1c81439-e169-48f3-8432-98934b0ee2d7',
                ],
            ],
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
                'name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale),
                'categoryHierarchy' => [[
                    'name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale),
                    'uuid' => 'dea6764b-a03b-4171-8243-46e730c8b90b',
                ],
                ],
            ],
            [
                'name' => t('Books', [], 'dataFixtures', $firstDomainLocale),
                'categoryHierarchy' => [[
                    'name' => t('Books', [], 'dataFixtures', $firstDomainLocale),
                    'uuid' => 'b4700dea-4b83-43d3-9df1-f162a114cacd',
                ],
                ],
            ],
            [
                'name' => t('Toys', [], 'dataFixtures', $firstDomainLocale),
                'categoryHierarchy' => [[
                    'name' => t('Toys', [], 'dataFixtures', $firstDomainLocale),
                    'uuid' => 'c5d5a03e-1d11-43e6-9b24-9058d13346b3',
                ],
                ],
            ],
            [
                'name' => t('Garden tools', [], 'dataFixtures', $firstDomainLocale),
                'categoryHierarchy' => [[
                    'name' => t('Garden tools', [], 'dataFixtures', $firstDomainLocale),
                    'uuid' => '8075297b-30b7-49e0-bd89-b4e5dec2f5b8',
                ],
                ],
            ],
            [
                'name' => t('Food', [], 'dataFixtures', $firstDomainLocale),
                'categoryHierarchy' => [[
                    'name' => t('Food', [], 'dataFixtures', $firstDomainLocale),
                    'uuid' => '57157d5a-b15b-495c-b689-65d395f1c50f',
                ],
                ],
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
                        'name' => t('TV, audio', [], 'dataFixtures', $firstDomainLocale),
                        'categoryHierarchy' => [
                            [
                                'name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale),
                                'uuid' => 'dea6764b-a03b-4171-8243-46e730c8b90b',
                            ],
                            [
                                'name' => t('TV, audio', [], 'dataFixtures', $firstDomainLocale),
                                'uuid' => 'c8154b52-af56-45cf-b8b7-36b00bf490c3',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Fotoaparáty',
                        'categoryHierarchy' => [
                            [
                                'name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale),
                                'uuid' => 'dea6764b-a03b-4171-8243-46e730c8b90b',
                            ],
                            [
                                'name' => 'Fotoaparáty',
                                'uuid' => '4b31f43c-ed0a-4db6-979b-10c27d7791f3',
                            ],
                        ],
                    ],
                    [
                        'name' => t('Printers', [], 'dataFixtures', $firstDomainLocale),
                        'categoryHierarchy' => [
                            [
                                'name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale),
                                'uuid' => 'dea6764b-a03b-4171-8243-46e730c8b90b',
                            ],
                            [
                                'name' => t('Printers', [], 'dataFixtures', $firstDomainLocale),
                                'uuid' => 'a1c81439-e169-48f3-8432-98934b0ee2d7',
                            ],
                        ],
                    ],
                    [
                        'name' => t('Personal Computers & accessories', [], 'dataFixtures', $firstDomainLocale),
                        'categoryHierarchy' => [
                            [
                                'name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale),
                                'uuid' => 'dea6764b-a03b-4171-8243-46e730c8b90b',
                            ],
                            [
                                'name' => t('Personal Computers & accessories', [], 'dataFixtures', $firstDomainLocale),
                                'uuid' => 'f79ade94-3f89-4244-b424-3911a1d82a64',
                            ],
                        ],
                    ],
                    [
                        'name' => t('Mobile Phones', [], 'dataFixtures', $firstDomainLocale),
                        'categoryHierarchy' => [
                            [
                                'name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale),
                                'uuid' => 'dea6764b-a03b-4171-8243-46e730c8b90b',
                            ],
                            [
                                'name' => t('Mobile Phones', [], 'dataFixtures', $firstDomainLocale),
                                'uuid' => '6eaaa31b-0b11-45c8-bee0-0423423a799a',
                            ],
                        ],
                    ],
                    [
                        'name' => t('Coffee Machines', [], 'dataFixtures', $firstDomainLocale),
                        'categoryHierarchy' => [
                            [
                                'name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale),
                                'uuid' => 'dea6764b-a03b-4171-8243-46e730c8b90b',
                            ],
                            [
                                'name' => t('Coffee Machines', [], 'dataFixtures', $firstDomainLocale),
                                'uuid' => '8d18e212-ca21-43d0-9a61-4677bc2ed0a7',
                            ],
                        ],
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
}
