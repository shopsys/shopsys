<?php

declare(strict_types=1);

namespace Tests\ReadModelBundle\Functional\Twig;

use Shopsys\FrameworkBundle\Component\Image\AdditionalImageData;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Twig\ImageExtension;
use Tests\App\Test\FunctionalTestCase;

class ImageExtensionTest extends FunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     * @inject
     */
    private $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageLocator
     * @inject
     */
    private $imageLocator;

    public function testGetImageHtmlWithMockedImageFacade(): void
    {
        $productId = 2;
        $entityName = 'product';
        $fileExtension = 'jpg';

        $imageFacadeMock = $this->createMock(ImageFacade::class);
        $imageFacadeMock->method('getImageUrlFromAttributes')->willReturn(sprintf('http://webserver:8080/%s/%d.%s', $entityName, $productId, $fileExtension));
        $imageFacadeMock->method('getAdditionalImagesDataFromAttributes')->willReturn([
            new AdditionalImageData('(min-width: 1200px)', sprintf('http://webserver:8080/%s/additional_0_%d.%s', $entityName, $productId, $fileExtension)),
            new AdditionalImageData('(max-width: 480px)', sprintf('http://webserver:8080/%s/additional_1_%d.%s', $entityName, $productId, $fileExtension)),
        ]);

        $imageView = new ImageView($productId, $fileExtension, $entityName, null);

        $readModelBundleImageExtension = $this->createImageExtension('', $imageFacadeMock, true);
        $html = $readModelBundleImageExtension->getImageHtml($imageView);

        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/Resources/picture.twig', $html);

        libxml_clear_errors();
    }

    public function testGetImageHtml(): void
    {
        $productId = 1;
        $entityName = 'product';
        $fileExtension = 'jpg';

        $imageView = new ImageView($productId, $fileExtension, $entityName, null);

        $readModelBundleImageExtension = $this->createImageExtension('', null, true);
        $html = $readModelBundleImageExtension->getImageHtml($imageView);

        $expected = '<picture>';
        $expected .= sprintf('    <source media="(min-width: 480px) and (max-width: 768px)" srcset="%s/content-test/images/product/default/additional_0_1.jpg"/>', $this->getCurrentUrl());
        $expected .= sprintf('    <img alt="" class="image-product" itemprop="image" data-src="%s/content-test/images/product/default/1.jpg" title="" src="" loading="lazy"/>', $this->getCurrentUrl());
        $expected .= '</picture>';

        $this->assertXmlStringEqualsXmlString($expected, $html);

        libxml_clear_errors();
    }

    public function testGetImageHtmlWithtoutLazyload(): void
    {
        $productId = 1;
        $entityName = 'product';
        $fileExtension = 'jpg';

        $imageView = new ImageView($productId, $fileExtension, $entityName, null);

        $readModelBundleImageExtension = $this->createImageExtension();
        $html = $readModelBundleImageExtension->getImageHtml($imageView, ['lazy' => false]);

        $expected = '<picture>';
        $expected .= sprintf('    <source media="(min-width: 480px) and (max-width: 768px)" srcset="%s/content-test/images/product/default/additional_0_1.jpg"/>', $this->getCurrentUrl());
        $expected .= sprintf('    <img alt="" class="image-product" itemprop="image" src="%s/content-test/images/product/default/1.jpg" title=""/>', $this->getCurrentUrl());
        $expected .= '</picture>';

        $this->assertXmlStringEqualsXmlString($expected, $html);

        libxml_clear_errors();
    }

    public function testGetNoImageHtml(): void
    {
        $readModelBundleImageExtension = $this->createImageExtension('', null, true);

        $html = $readModelBundleImageExtension->getImageHtml(null);

        $expected = '<picture>';
        $expected .= sprintf('    <img alt="" class="image-noimage" title=""  itemprop="image" data-src="%s/noimage.png" src="" loading="lazy"/>', $this->getCurrentUrl());
        $expected .= '</picture>';

        $this->assertXmlStringEqualsXmlString($expected, $html);

        libxml_clear_errors();
    }

    public function testGetNoImageHtmlWithDefaultFrontDesignImageUrlPrefix(): void
    {
        $defaultFrontDesignImageUrlPrefix = '/assets/frontend/images/';

        $readModelBundleImageExtension = $this->createImageExtension($defaultFrontDesignImageUrlPrefix, null, true);
        $html = $readModelBundleImageExtension->getImageHtml(null);

        $expected = '<picture>';
        $expected .= sprintf('    <img alt="" class="image-noimage" title=""  itemprop="image" data-src="%s%snoimage.png" src="" loading="lazy"/>', $this->getCurrentUrl(), $defaultFrontDesignImageUrlPrefix);
        $expected .= '</picture>';

        $this->assertXmlStringEqualsXmlString($expected, $html);

        libxml_clear_errors();
    }

    /**
     * @return string
     */
    private function getCurrentUrl(): string
    {
        return $this->domain->getCurrentDomainConfig()->getUrl();
    }

    /**
     * @param string $frontDesignImageUrlPrefix
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade|null $imageFacade
     * @param bool $enableLazyLoad
     * @return \Shopsys\ReadModelBundle\Twig\ImageExtension
     */
    private function createImageExtension(
        string $frontDesignImageUrlPrefix = '',
        ?ImageFacade $imageFacade = null,
        bool $enableLazyLoad = false
    ): ImageExtension {
        $templating = $this->getContainer()->get('twig');
        $imageFacade = $imageFacade ?: $this->imageFacade;

        return new ImageExtension($frontDesignImageUrlPrefix, $this->domain, $this->imageLocator, $imageFacade, $templating, $enableLazyLoad);
    }
}
