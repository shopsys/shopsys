<?php

namespace SS6\ShopBundle\Tests\Model\Image;

use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Model\Image\ImageFacade;
use SS6\ShopBundle\Model\Image\ImageLocator;

class ImageLocatorTest extends PHPUnit_Framework_TestCase {

	public function testGetRelativeImagePathProvider() {
		return array(
			array('entity', 'type', 'size', 'entity' . DIRECTORY_SEPARATOR . 'type' . DIRECTORY_SEPARATOR . 'size' . DIRECTORY_SEPARATOR),
			array('entity', 'type', null, 'entity' . DIRECTORY_SEPARATOR . 'type' . DIRECTORY_SEPARATOR),
			array('entity', null, 'size', 'entity' . DIRECTORY_SEPARATOR . 'size' . DIRECTORY_SEPARATOR),
			array('entity', null, null, 'entity' . DIRECTORY_SEPARATOR),
		);
	}

	/**
	 * @dataProvider testGetRelativeImagePathProvider
	 */
	public function testGetRelativeImagePath($entityName, $type, $sizeName, $expectedPath) {
		$imageFacadeMock = $this->getMock(ImageFacade::class, [], [], '', false);
		$imageLocator = new ImageLocator('imageDir', $imageFacadeMock);

		$this->assertEquals($expectedPath, $imageLocator->getRelativeImagePath($entityName, $type, $sizeName));
	}
}
