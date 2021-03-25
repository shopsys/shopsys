<?php

declare(strict_types=1);

namespace Tests\App\Functional\Component\Image\Config;

use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Product as BaseProduct;
use Tests\App\Test\FunctionalTestCase;

class ImageConfigTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\Config\ImageConfig
     * @inject
     */
    private $imageConfig;

    public function testGetImageConfigForExtendedEntity()
    {
        $baseProductImageConfig = $this->imageConfig->getImageEntityConfigByClass(BaseProduct::class);
        $projectProductImageConfig = $this->imageConfig->getImageEntityConfigByClass(Product::class);

        self::assertEquals($projectProductImageConfig, $baseProductImageConfig);
    }
}
