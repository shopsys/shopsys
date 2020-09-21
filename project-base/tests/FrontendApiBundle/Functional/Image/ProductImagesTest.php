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

    /**
     * @var mixed
     */
    private $webserverUrl;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = $this->productFacade->getById(1);
        $this->webserverUrl = $this->getContainer()->getParameter('overwrite_domain_url');
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
                    "url": "' . $this->webserverUrl . '/content-test/images/product/default/1.jpg",
                    "type": null,
                    "size": "default",
                    "width": 410,
                    "height": null,
                    "position": null
                },
                {
                    "url": "' . $this->webserverUrl . '/content-test/images/product/galleryThumbnail/1.jpg",
                    "type": null,
                    "size": "galleryThumbnail",
                    "width": null,
                    "height": 35,
                    "position": null
                },
                {
                    "url": "' . $this->webserverUrl . '/content-test/images/product/list/1.jpg",
                    "type": null,
                    "size": "list",
                    "width": 150,
                    "height": null,
                    "position": null
                },
                {
                    "url": "' . $this->webserverUrl . '/content-test/images/product/thumbnail/1.jpg",
                    "type": null,
                    "size": "thumbnail",
                    "width": 50,
                    "height": 40,
                    "position": null
                },
                {
                    "url": "' . $this->webserverUrl . '/content-test/images/product/original/1.jpg",
                    "type": null,
                    "size": "original",
                    "width": null,
                    "height": null,
                    "position": null
                },
                {
                    "url": "' . $this->webserverUrl . '/content-test/images/product/default/64.jpg",
                    "type": null,
                    "size": "default",
                    "width": 410,
                    "height": null,
                    "position": null
                },
                {
                    "url": "' . $this->webserverUrl . '/content-test/images/product/galleryThumbnail/64.jpg",
                    "type": null,
                    "size": "galleryThumbnail",
                    "width": null,
                    "height": 35,
                    "position": null
                },
                {
                    "url": "' . $this->webserverUrl . '/content-test/images/product/list/64.jpg",
                    "type": null,
                    "size": "list",
                    "width": 150,
                    "height": null,
                    "position": null
                },
                {
                    "url": "' . $this->webserverUrl . '/content-test/images/product/thumbnail/64.jpg",
                    "type": null,
                    "size": "thumbnail",
                    "width": 50,
                    "height": 40,
                    "position": null
                },
                {
                    "url": "' . $this->webserverUrl . '/content-test/images/product/original/64.jpg",
                    "type": null,
                    "size": "original",
                    "width": null,
                    "height": null,
                    "position": null
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
                    "url": "' . $this->webserverUrl . '/content-test/images/product/list/1.jpg",
                    "type": null,
                    "size": "list",
                    "width": 150,
                    "height": null,
                    "position": null
                },
                {
                    "url": "' . $this->webserverUrl . '/content-test/images/product/list/64.jpg",
                    "type": null,
                    "size": "list",
                    "width": 150,
                    "height": null,
                    "position": null
                }
            ]
        }
    }
}';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
