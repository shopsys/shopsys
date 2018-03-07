<?php

namespace Tests\ShopBundle\Unit\Component\Image\Config;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Image\Config\Exception\ImageTypeNotFoundException;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageEntityConfig;
use Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig;

class ImageEntityConfigTest extends TestCase
{
    public function testGetTypeSizes()
    {
        $types = [
            'TypeName_1' => [
                'SizeName_1_1' => $this->createImageSizeConfig('SizeName_1_1'),
                'SizeName_1_2' => $this->createImageSizeConfig('SizeName_1_2'),
            ],
            'TypeName_2' => [
                'SizeName_2_1' => $this->createImageSizeConfig('SizeName_2_1'),
            ],
        ];
        $sizes = [];

        $imageEntityConfig = new ImageEntityConfig('EntityName', 'EntityClass', $types, $sizes, []);

        $typeSizes = $imageEntityConfig->getSizeConfigsByType('TypeName_1');
        $this->assertSame($types['TypeName_1'], $typeSizes);
    }

    public function testGetTypeSizesNotFound()
    {
        $types = [
            'TypeName_1' => [
                'SizeName_1_1' => $this->createImageSizeConfig('SizeName_1_1'),
                'SizeName_1_2' => $this->createImageSizeConfig('SizeName_1_2'),
            ],
            'TypeName_2' => [
                'SizeName_2_1' => $this->createImageSizeConfig('SizeName_2_1'),
            ],
        ];
        $sizes = [];

        $imageEntityConfig = new ImageEntityConfig('EntityName', 'EntityClass', $types, $sizes, []);

        $this->expectException(ImageTypeNotFoundException::class);
        $imageEntityConfig->getSizeConfigsByType('TypeName_3');
    }

    public function testGetTypeSize()
    {
        $types = [
            'TypeName_1' => [
                'SizeName_1_1' => $this->createImageSizeConfig('SizeName_1_1'),
                'SizeName_1_2' => $this->createImageSizeConfig('SizeName_1_2'),
            ],
            'TypeName_2' => [
                ImageEntityConfig::WITHOUT_NAME_KEY => $this->createImageSizeConfig(null),
            ],
        ];
        $sizes = [
            ImageEntityConfig::WITHOUT_NAME_KEY => $this->createImageSizeConfig(null),
        ];

        $imageEntityConfig = new ImageEntityConfig('EntityName', 'EntityClass', $types, $sizes, []);

        $size1 = $imageEntityConfig->getSizeConfigByType(null, null);
        $this->assertSame($sizes[ImageEntityConfig::WITHOUT_NAME_KEY], $size1);

        $type1Size1 = $imageEntityConfig->getSizeConfigByType('TypeName_1', 'SizeName_1_1');
        $this->assertSame($types['TypeName_1']['SizeName_1_1'], $type1Size1);

        $type2Size1 = $imageEntityConfig->getSizeConfigByType('TypeName_2', null);
        $this->assertSame($types['TypeName_2'][ImageEntityConfig::WITHOUT_NAME_KEY], $type2Size1);
    }

    /**
     * @param string $name
     * @return \Shopsys\FrameworkBundle\Component\Image\Config\ImageSizeConfig
     */
    private function createImageSizeConfig($name)
    {
        return new ImageSizeConfig($name, null, null, false, null);
    }
}
