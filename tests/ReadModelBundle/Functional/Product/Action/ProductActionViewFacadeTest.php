<?php

declare(strict_types=1);

namespace Tests\ReadModelBundle\Functional\Product\Action;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Action\ProductActionView;
use Tests\App\Test\FunctionalTestCase;

class ProductActionViewFacadeTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade
     * @inject
     */
    private $productActionViewFacade;

    public function testGetForSingleProduct(): void
    {
        $url = $this->domain->getUrl();

        $products = [
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1'),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2'),
            $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '3'),
        ];

        $productActionViews = $this->productActionViewFacade->getForProducts($products);

        $expected = [
            1 => new ProductActionView(1, false, false, sprintf('%s/produkt/televize-22-sencor-sle-22f46dm4-hello-kitty-plazmova-9177759', $url), null),
            2 => new ProductActionView(2, false, false, sprintf('%s/produkt/32-philips-32pfl4308-9176508', $url), null),
            3 => new ProductActionView(3, false, false, sprintf('%s/produkt/47-lg-47la790v-fhd-5965879p', $url), null),
        ];

        $this->assertEquals($expected, $productActionViews);
    }
}
