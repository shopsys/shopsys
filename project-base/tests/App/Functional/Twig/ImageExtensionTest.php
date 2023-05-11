<?php

declare(strict_types=1);

namespace Tests\App\Functional\Twig;

use Shopsys\FrameworkBundle\Component\Image\AdditionalImageData;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Component\Image\ImageRepository;
use Shopsys\FrameworkBundle\Twig\ImageExtension;
use Tests\App\Test\FunctionalTestCase;
use Zalas\Injector\PHPUnit\Symfony\TestCase\SymfonyTestContainer;

class ImageExtensionTest extends FunctionalTestCase
{
    use SymfonyTestContainer;

    /**
     * @inject
     */
    private ImageLocator $imageLocator;

    public function testGetImageHtmlWithAdditional(): void
    {
        $templating = self::getContainer()->get('twig');

        $imageFacade = $this->createMock(ImageFacade::class);

        /** @var \Shopsys\FrameworkBundle\Component\Image\ImageRepository $imageRepository */
        $imageRepository = $this->getContainer()->get(ImageRepository::class);
        $image = $imageRepository->getById(1);

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
