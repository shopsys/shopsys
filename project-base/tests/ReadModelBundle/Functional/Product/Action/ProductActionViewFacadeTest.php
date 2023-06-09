<?php

declare(strict_types=1);

namespace Tests\ReadModelBundle\Functional\Product\Action;

use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\ReadModelBundle\Product\Action\ProductActionView;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacadeInterface;
use Tests\App\Test\FunctionalTestCase;

class ProductActionViewFacadeTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private ProductActionViewFacadeInterface $productActionViewFacade;

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
            1 => new ProductActionView(1, false, false, sprintf('%s/22-sencor-sle-22f46dm4-hello-kitty/', $url)),
            2 => new ProductActionView(2, false, false, sprintf('%s/32-philips-32pfl4308/', $url)),
            3 => new ProductActionView(3, false, false, sprintf('%s/47-lg-47la790v-fhd/', $url)),
        ];

        $this->assertEquals($expected, $productActionViews);
    }
}
