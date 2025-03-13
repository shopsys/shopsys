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

        $helloKittyName = t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $helloKittySlug = $this->transformStringHelper->stringToFriendlyUrlSlug($helloKittyName);

        $expectedData = [
            'images' => [
                [
                    'url' => $this->getFullUrlPath('/content-test/images/product/' . $helloKittySlug . '_1.jpg'),
                    'name' => 'Product 1 image',
                ],
                [
                    'url' => $this->getFullUrlPath('/content-test/images/product/' . $helloKittySlug . '_64.jpg'),
                    'name' => 'Product 1 image',
                ],
            ],
        ];

        $this->assertSame($expectedData, $responseData);
    }

    public function testFirstTwoProductsWithAllImagesAndCategoriesWithAllImages(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductsQuery.graphql', [
            'first' => 2,
        ]);
        $responseData = $this->getResponseDataForGraphQlType($response, 'products');

        $personalComputersAndAccessoriesName = t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $personalComputersAndAccessoriesSlug = $this->transformStringHelper->stringToFriendlyUrlSlug($personalComputersAndAccessoriesName);

        $booksName = t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $booksSlug = $this->transformStringHelper->stringToFriendlyUrlSlug($booksName);

        $helloKittyName = t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $helloKittySlug = $this->transformStringHelper->stringToFriendlyUrlSlug($helloKittyName);

        $electronicsName = t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $electronicsSlug = $this->transformStringHelper->stringToFriendlyUrlSlug($electronicsName);

        $tvAudioName = t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $tvAudioSlug = $this->transformStringHelper->stringToFriendlyUrlSlug($tvAudioName);

        $expectedData = [
            'edges' => [
                [
                    'node' => [
                        'images' => [],
                        'categories' => [
                            [
                                'images' => [
                                    [
                                        'url' => $this->getFullUrlPath('/content-test/images/category/' . $booksSlug . '_75.jpg'),
                                        'name' => CategoryDataFixture::CATEGORY_BOOKS,
                                    ],
                                ],
                            ],
                            [
                                'images' => [
                                    [
                                        'url' => $this->getFullUrlPath('/content-test/images/category/' . $personalComputersAndAccessoriesSlug . '_72.jpg'),
                                        'name' => CategoryDataFixture::CATEGORY_PC,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'node' => [
                        'images' => [
                            [
                                'url' => $this->getFullUrlPath('/content-test/images/product/' . $helloKittySlug . '_1.jpg'),
                                'name' => 'Product 1 image',
                            ],
                            [
                                'url' => $this->getFullUrlPath('/content-test/images/product/' . $helloKittySlug . '_64.jpg'),
                                'name' => 'Product 1 image',
                            ],
                        ],
                        'categories' => [
                            [
                                'images' => [
                                    [
                                        'url' => $this->getFullUrlPath('/content-test/images/category/' . $electronicsSlug . '_68.jpg'),
                                        'name' => CategoryDataFixture::CATEGORY_ELECTRONICS,
                                    ],
                                ],
                            ],
                            [
                                'images' => [
                                    [
                                        'url' => $this->getFullUrlPath('/content-test/images/category/' . $tvAudioSlug . '_69.jpg'),
                                        'name' => CategoryDataFixture::CATEGORY_TV,
                                    ],
                                ],
                            ],
                            [
                                'images' => [
                                    [
                                        'url' => $this->getFullUrlPath('/content-test/images/category/' . $personalComputersAndAccessoriesSlug . '_72.jpg'),
                                        'name' => CategoryDataFixture::CATEGORY_PC,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $this->assertSame($expectedData, $responseData);
    }
}
