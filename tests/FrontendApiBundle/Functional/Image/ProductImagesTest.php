<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Image;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductImagesTest extends GraphQlTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    private $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     * @inject
     */
    private $productFacade;

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
                            "height": null
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "galleryThumbnail",
                            "width": 64,
                            "height": 64
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/modal/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "modal",
                            "width": 96,
                            "height": null
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/list/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "list",
                            "width": 160,
                            "height": 160
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnail/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "thumbnail",
                            "width": 90,
                            "height": 63
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailSmall/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "thumbnailSmall",
                            "width": 43,
                            "height": 28
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailExtraSmall/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "thumbnailExtraSmall",
                            "width": 54,
                            "height": 54
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailMedium/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "thumbnailMedium",
                            "width": 72,
                            "height": 48
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/original/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                            "size": "original",
                            "width": null,
                            "height": null
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
                            "height": null
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "galleryThumbnail",
                            "width": 64,
                            "height": 64
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/modal/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "modal",
                            "width": 96,
                            "height": null
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/list/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "list",
                            "width": 160,
                            "height": 160
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnail/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "thumbnail",
                            "width": 90,
                            "height": 63
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailSmall/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "thumbnailSmall",
                            "width": 43,
                            "height": 28
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailExtraSmall/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "thumbnailExtraSmall",
                            "width": 54,
                            "height": 54
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailMedium/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "thumbnailMedium",
                            "width": 72,
                            "height": 48
                        },
                        {
                            "url": "' . $this->getFullUrlPath('/content-test/images/product/original/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                            "size": "original",
                            "width": null,
                            "height": null
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
}
