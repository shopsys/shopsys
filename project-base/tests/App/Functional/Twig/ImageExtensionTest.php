<?php

declare(strict_types=1);

namespace Tests\App\Functional\Twig;

use Shopsys\FrameworkBundle\Component\Image\AdditionalImageData;
use Shopsys\FrameworkBundle\Component\Image\Image;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Twig\ImageExtension;
use Tests\App\Test\FunctionalTestCase;

class ImageExtensionTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageLocator
     * @inject
     */
    private $imageLocator;

    public function testGetImageHtmlWithAdditional(): void
    {
        $templating = $this->getContainer()->get('twig');

        $imageFacade = $this->createMock(ImageFacade::class);

        $image = new Image('product', 2, null, null);

        $imageFacade->method('getImageByObject')->willReturn($image);
        $imageFacade->method('getImageUrl')->willReturn('http://webserver:8080/2.jpg');
        $imageFacade->method('getAdditionalImagesData')->willReturn([
            new AdditionalImageData('(min-width: 1200px)', 'http://webserver:8080/additional_0_2.jpg'),
            new AdditionalImageData('(max-width: 480px)', 'http://webserver:8080/additional_1_2.jpg'),
        ]);

        $imageExtension = new ImageExtension('', $this->domain, $this->imageLocator, $imageFacade, $templating, true);

        $html = $imageExtension->getImageHtml($image);

        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/Resources/picture.twig', $html);

        libxml_clear_errors();
    }
}
