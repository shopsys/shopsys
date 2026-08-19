<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Image;

use App\DataFixtures\Demo\CategoryDataFixture;
use Override;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductImagesTest extends GraphQlTestCase
{
    private Product $product;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private TransformStringHelper $transformStringHelper;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->productFacade->getById(1);
    }

    public function testFirstProductWithAllImages(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductQuery.graphql', [
            'productUuid' => $this->product->getUuid(),
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'product');

        $allImages = [
            $this->getExpectedProductImage(1, 'Front view of %productName%'),
            $this->getExpectedProductImage(64, 'Remote control of %productName%'),
        ];

        $expectedData = [
            'images' => $allImages,
            'mainImage' => $allImages[0],
        ];

        $this->assertSame($expectedData, $responseData);
    }

    public function testFirstTwoProductsWithAllImagesAndCategoriesWithAllImages(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductsQuery.graphql', [
            'first' => 2,
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'products');

        $this->assertSame(
            [
                'edges' => [
                    [
                        'node' => [
                            'images' => [],
                            'categories' => $this->getExpectedFirstProductCategories(),
                        ],
                    ],
                    [
                        'node' => [
                            'images' => [
                                $this->getExpectedProductImage(1, 'Front view of %productName%'),
                                $this->getExpectedProductImage(64, 'Remote control of %productName%'),
                            ],
                            'categories' => $this->getExpectedSecondProductCategories(),
                        ],
                    ],
                ],
            ],
            $responseData,
        );
    }

    public function testFirstTwoProductsImagesCount(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductImagesCountQuery.graphql', [
            'first' => 2,
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'products');

        $this->assertSame(
            [
                'edges' => [
                    ['node' => ['imagesCount' => 0]],
                    ['node' => ['imagesCount' => 2]],
                ],
            ],
            $responseData,
        );
    }

    /**
     * @return array{url: string, name: string}
     */
    private function getExpectedProductImage(int $imageId, string $nameTranslationKey): array
    {
        $locale = $this->getFirstDomainLocale();
        $productName = t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $locale);
        $productSlug = $this->transformStringHelper->stringToFriendlyUrlSlug($productName);
        $imageName = t(
            $nameTranslationKey,
            ['%productName%' => $this->product->getFullName($locale)],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $locale,
        );

        return [
            'url' => $this->getBaseUrlPath('/content-test/images/product/' . $productSlug . '_' . $imageId . '.jpg'),
            'name' => $imageName,
        ];
    }

    /**
     * @return array{url: string, name: string}
     */
    private function getExpectedCategoryImage(
        string $categoryTranslationKey,
        string $categoryName,
        int $imageId,
    ): array {
        $translatedCategoryName = t(
            $categoryTranslationKey,
            [],
            Translator::DATA_FIXTURES_TRANSLATION_DOMAIN,
            $this->getFirstDomainLocale(),
        );
        $categorySlug = $this->transformStringHelper->stringToFriendlyUrlSlug($translatedCategoryName);

        return [
            'url' => $this->getBaseUrlPath('/content-test/images/category/' . $categorySlug . '_' . $imageId . '.jpg'),
            'name' => $categoryName,
        ];
    }

    private function getExpectedFirstProductCategories(): array
    {
        return [
            [
                'images' => [
                    $this->getExpectedCategoryImage('Books', CategoryDataFixture::CATEGORY_BOOKS, 75),
                ],
            ],
            [
                'images' => [
                    $this->getExpectedCategoryImage(
                        'Personal Computers & accessories',
                        CategoryDataFixture::CATEGORY_PC,
                        72,
                    ),
                ],
            ],
        ];
    }

    private function getExpectedSecondProductCategories(): array
    {
        return [
            [
                'images' => [
                    $this->getExpectedCategoryImage(
                        'Electronics',
                        CategoryDataFixture::CATEGORY_ELECTRONICS,
                        68,
                    ),
                ],
            ],
            [
                'images' => [
                    $this->getExpectedCategoryImage('TV, audio', CategoryDataFixture::CATEGORY_TV, 69),
                ],
            ],
            [
                'images' => [
                    $this->getExpectedCategoryImage(
                        'Personal Computers & accessories',
                        CategoryDataFixture::CATEGORY_PC,
                        72,
                    ),
                ],
            ],
        ];
    }
}
