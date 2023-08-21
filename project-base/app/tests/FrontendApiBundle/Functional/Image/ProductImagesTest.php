<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Image;

use Shopsys\FrameworkBundle\Component\String\TransformString;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->productFacade->getById(1);
    }

    public function testFirstProductWithAllImages(): void
    {
        $query = '
            query {
                product(uuid: "' . $this->product->getUuid() . '") {
                    images {
                        position
                        type
                        sizes {
                            url
                            size
                            width
                            height
                            additionalSizes {
                                url
                                width
                                height
                                media
                            }
                        }
                    }
                }
            }
        ';

        $helloKittyName = t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $helloKittySlug = TransformString::stringToFriendlyUrlSlug($helloKittyName);

        $jsonExpected = '
{
    "data": {
        "product": {
            "images": [
                {
                    "position": 0,
                    "type": null,
                    "sizes": [
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/default/' . $helloKittySlug . '_1.jpg') . '",
                            "size": "default",
                            "width": 605,
                            "height": null,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/default/0--' . $helloKittySlug . '_1.jpg') . '",
                                  "width": 710,
                                  "height": null,
                                  "media": "(min-width: 480px) and (max-width: 768px)"
                                },
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/default/1--' . $helloKittySlug . '_1.jpg') . '",
                                  "width": 1210,
                                  "height": null,
                                  "media": "only screen and (min-width: 769px) and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/' . $helloKittySlug . '_1.jpg') . '",
                            "size": "galleryThumbnail",
                            "width": 64,
                            "height": 64,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/0--' . $helloKittySlug . '_1.jpg') . '",
                                  "width": 128,
                                  "height": 128,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                },
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/1--' . $helloKittySlug . '_1.jpg') . '",
                                  "width": 1210,
                                  "height": null,
                                  "media": "only screen and (max-width: 768px) and (-webkit-min-device-pixel-ratio: 1.5)"
                                },
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/2--' . $helloKittySlug . '_1.jpg') . '",
                                  "width": 605,
                                  "height": null,
                                  "media": "(max-width: 768px)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/modal/' . $helloKittySlug . '_1.jpg') . '",
                            "size": "modal",
                            "width": 96,
                            "height": null,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/modal/0--' . $helloKittySlug . '_1.jpg') . '",
                                  "width": 192,
                                  "height": null,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/list/' . $helloKittySlug . '_1.jpg') . '",
                            "size": "list",
                            "width": 160,
                            "height": 160,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/list/0--' . $helloKittySlug . '_1.jpg') . '",
                                  "width": 320,
                                  "height": 320,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnail/' . $helloKittySlug . '_1.jpg') . '",
                            "size": "thumbnail",
                            "width": 90,
                            "height": 63,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnail/0--' . $helloKittySlug . '_1.jpg') . '",
                                  "width": 180,
                                  "height": 126,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailSmall/' . $helloKittySlug . '_1.jpg') . '",
                            "size": "thumbnailSmall",
                            "width": 43,
                            "height": 28,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailSmall/0--' . $helloKittySlug . '_1.jpg') . '",
                                  "width": 86,
                                  "height": 56,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailExtraSmall/' . $helloKittySlug . '_1.jpg') . '",
                            "size": "thumbnailExtraSmall",
                            "width": 54,
                            "height": 54,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailExtraSmall/0--' . $helloKittySlug . '_1.jpg') . '",
                                  "width": 108,
                                  "height": 108,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailMedium/' . $helloKittySlug . '_1.jpg') . '",
                            "size": "thumbnailMedium",
                            "width": 72,
                            "height": 48,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailMedium/0--' . $helloKittySlug . '_1.jpg') . '",
                                  "width": 144,
                                  "height": 96,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/original/' . $helloKittySlug . '_1.jpg') . '",
                            "size": "original",
                            "width": null,
                            "height": null,
                            "additionalSizes": []
                        }
                    ]
                },
                {
                    "position": 1,
                    "type": null,
                    "sizes": [
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/default/' . $helloKittySlug . '_64.jpg') . '",
                            "size": "default",
                            "width": 605,
                            "height": null,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/default/0--' . $helloKittySlug . '_64.jpg') . '",
                                  "width": 710,
                                  "height": null,
                                  "media": "(min-width: 480px) and (max-width: 768px)"
                                },
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/default/1--' . $helloKittySlug . '_64.jpg') . '",
                                  "width": 1210,
                                  "height": null,
                                  "media": "only screen and (min-width: 769px) and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/' . $helloKittySlug . '_64.jpg') . '",
                            "size": "galleryThumbnail",
                            "width": 64,
                            "height": 64,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/0--' . $helloKittySlug . '_64.jpg') . '",
                                  "width": 128,
                                  "height": 128,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                },
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/1--' . $helloKittySlug . '_64.jpg') . '",
                                  "width": 1210,
                                  "height": null,
                                  "media": "only screen and (max-width: 768px) and (-webkit-min-device-pixel-ratio: 1.5)"
                                },
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/2--' . $helloKittySlug . '_64.jpg') . '",
                                  "width": 605,
                                  "height": null,
                                  "media": "(max-width: 768px)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/modal/' . $helloKittySlug . '_64.jpg') . '",
                            "size": "modal",
                            "width": 96,
                            "height": null,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/modal/0--' . $helloKittySlug . '_64.jpg') . '",
                                  "width": 192,
                                  "height": null,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/list/' . $helloKittySlug . '_64.jpg') . '",
                            "size": "list",
                            "width": 160,
                            "height": 160,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/list/0--' . $helloKittySlug . '_64.jpg') . '",
                                  "width": 320,
                                  "height": 320,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnail/' . $helloKittySlug . '_64.jpg') . '",
                            "size": "thumbnail",
                            "width": 90,
                            "height": 63,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnail/0--' . $helloKittySlug . '_64.jpg') . '",
                                  "width": 180,
                                  "height": 126,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailSmall/' . $helloKittySlug . '_64.jpg') . '",
                            "size": "thumbnailSmall",
                            "width": 43,
                            "height": 28,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailSmall/0--' . $helloKittySlug . '_64.jpg') . '",
                                  "width": 86,
                                  "height": 56,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailExtraSmall/' . $helloKittySlug . '_64.jpg') . '",
                            "size": "thumbnailExtraSmall",
                            "width": 54,
                            "height": 54,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailExtraSmall/0--' . $helloKittySlug . '_64.jpg') . '",
                                  "width": 108,
                                  "height": 108,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailMedium/' . $helloKittySlug . '_64.jpg') . '",
                            "size": "thumbnailMedium",
                            "width": 72,
                            "height": 48,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailMedium/0--' . $helloKittySlug . '_64.jpg') . '",
                                  "width": 144,
                                  "height": 96,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/original/' . $helloKittySlug . '_64.jpg') . '",
                            "size": "original",
                            "width": null,
                            "height": null,
                            "additionalSizes": []
                        }
                    ]
                }
            ]
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    public function testFirstProductWithListImages(): void
    {
        $query = '
            query {
                product(uuid: "' . $this->product->getUuid() . '") {
                    images(sizes: ["list"]) {
                        position
                        type
                        sizes {
                            url
                            size
                            width
                            height
                        }
                    }
                }
            }
        ';

        $helloKittyName = t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $helloKittySlug = TransformString::stringToFriendlyUrlSlug($helloKittyName);

        $jsonExpected = '
{
    "data": {
        "product": {
            "images": [
                {
                    "position": 0,
                    "type": null,
                    "sizes": [
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/list/' . $helloKittySlug . '_1.jpg') . '",
                            "size": "list",
                            "width": 160,
                            "height": 160
                        }
                    ]
                },
                {
                    "position": 1,
                    "type": null,
                    "sizes": [
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/list/' . $helloKittySlug . '_64.jpg') . '",
                            "size": "list",
                            "width": 160,
                            "height": 160
                        }
                    ]
                }
            ]
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }

    public function testFirstTwoProductsWithListImagesAndCategoriesWithAllImages(): void
    {
        $query = '
            query {
  products(first:2) {
    edges {
      node {
        images(sizes:["list"]) {
          sizes {
            url
          }
        }
        categories {
          images {
            sizes {
              url
            }            
          }            
        }
      }
    }
  }
}
        ';

        $personalComputersAndAccessoriesName = t('Personal Computers & accessories', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $personalComputersAndAccessoriesSlug = TransformString::stringToFriendlyUrlSlug($personalComputersAndAccessoriesName);

        $booksName = t('Books', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $booksSlug = TransformString::stringToFriendlyUrlSlug($booksName);

        $helloKittyName = t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $helloKittySlug = TransformString::stringToFriendlyUrlSlug($helloKittyName);

        $electronicsName = t('Electronics', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $electronicsSlug = TransformString::stringToFriendlyUrlSlug($electronicsName);

        $tvAudioName = t('TV, audio', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale());
        $tvAudioSlug = TransformString::stringToFriendlyUrlSlug($tvAudioName);

        $jsonExpected = '{
  "data": {
    "products": {
      "edges": [
        {
          "node": {
            "images": [],
            "categories": [
              {
                "images": [
                  {
                    "sizes": [
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/default/' . $booksSlug . '_75.jpg') . '"
                      },
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/original/' . $booksSlug . '_75.jpg') . '"
                      }
                    ]
                  }
                ]
              }, 
              {
                "images": [
                  {
                    "sizes": [
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/default/' . $personalComputersAndAccessoriesSlug . '_72.jpg') . '"
                      },
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/original/' . $personalComputersAndAccessoriesSlug . '_72.jpg') . '"
                      }
                    ]
                  }
                ]
              }
            ]
          }
        },
        {
          "node": {
            "images": [
              {
                "sizes": [
                  {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/list/' . $helloKittySlug . '_1.jpg') . '"
                  }
                ]
              },
              {
                "sizes": [
                  {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/list/' . $helloKittySlug . '_64.jpg') . '"
                  }
                ]
              }
            ],
            "categories": [
              {
                "images": [
                  {
                    "sizes": [
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/default/' . $electronicsSlug . '_68.jpg') . '"
                      },
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/original/' . $electronicsSlug . '_68.jpg') . '"
                      }
                    ]
                  }
                ]
              },
              {
                "images": [
                  {
                    "sizes": [
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/default/' . $tvAudioSlug . '_69.jpg') . '"
                      },
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/original/' . $tvAudioSlug . '_69.jpg') . '"
                      }
                    ]
                  }
                ]
              },
              {
                "images": [
                  {
                    "sizes": [
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/default/' . $personalComputersAndAccessoriesSlug . '_72.jpg') . '"
                      },
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/original/' . $personalComputersAndAccessoriesSlug . '_72.jpg') . '"
                      }
                    ]
                  }
                ]
              }
            ]
          }
        }
      ]
    }
  }
}
        ';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
