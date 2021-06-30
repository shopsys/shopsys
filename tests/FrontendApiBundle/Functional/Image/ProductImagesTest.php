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
                    images{
                        url,
                        type,
                        size,
                        width,
                        height,
                        position
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
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/default/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                    "type": null,
                    "size": "default",
                    "width": 605,
                    "height": null,
                    "position": 0
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                    "type": null,
                    "size": "galleryThumbnail",
                    "width": 200,
                    "height": null,
                    "position": 0
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/modal/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                    "type": null,
                    "size": "modal",
                    "width": 96,
                    "height": null,
                    "position": 0
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/list/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                    "type": null,
                    "size": "list",
                    "width": 190,
                    "height": 190,
                    "position": 0
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnail/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                    "type": null,
                    "size": "thumbnail",
                    "width": 90,
                    "height": 63,
                    "position": 0
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailSmall/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                    "type": null,
                    "size": "thumbnailSmall",
                    "width": 43,
                    "height": 28,
                    "position": 0
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailExtraSmall/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                    "type": null,
                    "size": "thumbnailExtraSmall",
                    "width": 54,
                    "height": 54,
                    "position": 0
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailMedium/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                    "type": null,
                    "size": "thumbnailMedium",
                    "width": 72,
                    "height": 48,
                    "position": 0
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/original/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                    "type": null,
                    "size": "original",
                    "width": null,
                    "height": null,
                    "position": 0
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/default/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                    "type": null,
                    "size": "default",
                    "width": 605,
                    "height": null,
                    "position": 1
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/galleryThumbnail/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                    "type": null,
                    "size": "galleryThumbnail",
                    "width": 200,
                    "height": null,
                    "position": 1
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/modal/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                    "type": null,
                    "size": "modal",
                    "width": 96,
                    "height": null,
                    "position": 1
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/list/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                    "type": null,
                    "size": "list",
                    "width": 190,
                    "height": 190,
                    "position": 1
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnail/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                    "type": null,
                    "size": "thumbnail",
                    "width": 90,
                    "height": 63,
                    "position": 1
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailSmall/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                    "type": null,
                    "size": "thumbnailSmall",
                    "width": 43,
                    "height": 28,
                    "position": 1
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailExtraSmall/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                    "type": null,
                    "size": "thumbnailExtraSmall",
                    "width": 54,
                    "height": 54,
                    "position": 1
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/thumbnailMedium/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                    "type": null,
                    "size": "thumbnailMedium",
                    "width": 72,
                    "height": 48,
                    "position": 1
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/original/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                    "type": null,
                    "size": "original",
                    "width": null,
                    "height": null,
                    "position": 1
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
                    images(size: "list") {
                        url,
                        type,
                        size,
                        width,
                        height,
                        position
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
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/list/22-sencor-sle-22f46dm4-hello-kitty_1.jpg') . '",
                    "type": null,
                    "size": "list",
                    "width": 190,
                    "height": 190,
                    "position": 0
                },
                {
                    "url": "' . $this->getFullUrlPath('/content-test/images/product/list/22-sencor-sle-22f46dm4-hello-kitty_64.jpg') . '",
                    "type": null,
                    "size": "list",
                    "width": 190,
                    "height": 190,
                    "position": 1
                }
            ]
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
