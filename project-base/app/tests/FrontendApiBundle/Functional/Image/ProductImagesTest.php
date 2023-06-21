<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Image;

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
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/default/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "default",
                            "width": 605,
                            "height": null,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/default/0--22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                                  "width": 710,
                                  "height": null,
                                  "media": "(min-width: 480px) and (max-width: 768px)"
                                },
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/default/1--22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                                  "width": 1210,
                                  "height": null,
                                  "media": "only screen and (min-width: 769px) and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "galleryThumbnail",
                            "width": 64,
                            "height": 64,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/0--22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                                  "width": 128,
                                  "height": 128,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                },
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/1--22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                                  "width": 1210,
                                  "height": null,
                                  "media": "only screen and (max-width: 768px) and (-webkit-min-device-pixel-ratio: 1.5)"
                                },
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/2--22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                                  "width": 605,
                                  "height": null,
                                  "media": "(max-width: 768px)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/modal/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "modal",
                            "width": 96,
                            "height": null,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/modal/0--22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                                  "width": 192,
                                  "height": null,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/list/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "list",
                            "width": 160,
                            "height": 160,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/list/0--22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                                  "width": 320,
                                  "height": 320,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnail/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "thumbnail",
                            "width": 90,
                            "height": 63,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnail/0--22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                                  "width": 180,
                                  "height": 126,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailSmall/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "thumbnailSmall",
                            "width": 43,
                            "height": 28,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailSmall/0--22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                                  "width": 86,
                                  "height": 56,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailExtraSmall/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "thumbnailExtraSmall",
                            "width": 54,
                            "height": 54,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailExtraSmall/0--22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                                  "width": 108,
                                  "height": 108,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailMedium/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "thumbnailMedium",
                            "width": 72,
                            "height": 48,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailMedium/0--22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                                  "width": 144,
                                  "height": 96,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/original/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
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
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/default/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "default",
                            "width": 605,
                            "height": null,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/default/0--22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                                  "width": 710,
                                  "height": null,
                                  "media": "(min-width: 480px) and (max-width: 768px)"
                                },
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/default/1--22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                                  "width": 1210,
                                  "height": null,
                                  "media": "only screen and (min-width: 769px) and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "galleryThumbnail",
                            "width": 64,
                            "height": 64,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/0--22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                                  "width": 128,
                                  "height": 128,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                },
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/1--22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                                  "width": 1210,
                                  "height": null,
                                  "media": "only screen and (max-width: 768px) and (-webkit-min-device-pixel-ratio: 1.5)"
                                },
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/2--22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                                  "width": 605,
                                  "height": null,
                                  "media": "(max-width: 768px)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/modal/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "modal",
                            "width": 96,
                            "height": null,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/modal/0--22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                                  "width": 192,
                                  "height": null,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/list/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "list",
                            "width": 160,
                            "height": 160,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/list/0--22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                                  "width": 320,
                                  "height": 320,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnail/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "thumbnail",
                            "width": 90,
                            "height": 63,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnail/0--22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                                  "width": 180,
                                  "height": 126,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailSmall/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "thumbnailSmall",
                            "width": 43,
                            "height": 28,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailSmall/0--22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                                  "width": 86,
                                  "height": 56,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailExtraSmall/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "thumbnailExtraSmall",
                            "width": 54,
                            "height": 54,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailExtraSmall/0--22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                                  "width": 108,
                                  "height": 108,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailMedium/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "thumbnailMedium",
                            "width": 72,
                            "height": 48,
                            "additionalSizes": [
                                {
                                  "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailMedium/0--22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                                  "width": 144,
                                  "height": 96,
                                  "media": "only screen and (-webkit-min-device-pixel-ratio: 1.5)"
                                }
                            ]
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/original/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
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
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/list/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
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
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/list/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
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
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/default/books_75.jpg') . '"
                      },
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/original/books_75.jpg') . '"
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
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/default/personal-computers-accessories_72.jpg') . '"
                      },
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/original/personal-computers-accessories_72.jpg') . '"
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
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/list/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '"
                  }
                ]
              },
              {
                "sizes": [
                  {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/list/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '"
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
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/default/electronics_68.jpg') . '"
                      },
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/original/electronics_68.jpg') . '"
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
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/default/tv-audio_69.jpg') . '"
                      },
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/original/tv-audio_69.jpg') . '"
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
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/default/personal-computers-accessories_72.jpg') . '"
                      },
                      {
                        "url": "' . $this->getFullUrlPath('/content-test/images/category/original/personal-computers-accessories_72.jpg') . '"
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
