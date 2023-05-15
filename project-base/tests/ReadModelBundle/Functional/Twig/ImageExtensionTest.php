<?php

declare(strict_types=1);

namespace Tests\ReadModelBundle\Functional\Twig;

use ReflectionProperty;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\AdditionalImageData;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageLocator;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\ReadModelBundle\Image\ImageView;
use Shopsys\ReadModelBundle\Twig\ImageExtension;
use Sinergi\BrowserDetector\Browser;
use Tests\App\Test\FunctionalTestCase;

class ImageExtensionTest extends FunctionalTestCase
{
    /**
     * @inject
     */
    private ImageFacade $imageFacade;

    /**
     * @inject
     */
    private ImageLocator $imageLocator;

    private Domain $domainMock;

    public function testGetImageHtmlWithMockedImageFacade(): void
    {
        $productId = 2;
        $entityName = 'product';
        $fileExtension = 'jpg';

        $imageFacadeMock = $this->createMock(ImageFacade::class);
        $imageFacadeMock->method('getImageUrlFromAttributes')->willReturn(
            sprintf('http://webserver:8080/%s/%d.%s', $entityName, $productId, $fileExtension),
        );
        $imageFacadeMock->method('getAdditionalImagesDataFromAttributes')->willReturn([
            new AdditionalImageData(
                '(min-width: 1200px)',
                sprintf('http://webserver:8080/%s/additional_0_%d.%s', $entityName, $productId, $fileExtension),
            ),
            new AdditionalImageData(
                '(max-width: 480px)',
                sprintf('http://webserver:8080/%s/additional_1_%d.%s', $entityName, $productId, $fileExtension),
            ),
        ]);

        $imageView = new ImageView($productId, $fileExtension, $entityName, null);

        $readModelBundleImageExtension = $this->createImageExtension('', $imageFacadeMock, true);
        $html = $readModelBundleImageExtension->getImageHtml($imageView);

        $this->assertXmlStringEqualsXmlFile(__DIR__ . '/Resources/picture.twig', $html);

        libxml_clear_errors();
    }

    public function testGetImageHtml(): void
    {
        $imageView = $this->createImageView();

        $readModelBundleImageExtension = $this->createImageExtension('', null, true);
        $html = $readModelBundleImageExtension->getImageHtml($imageView);

        $expected = '<picture>';
        $expected .= sprintf(
            '    <source media="(min-width: 480px) and (max-width: 768px)" srcset="%s/content-test/images/product/default/additional_0_1.jpg"/>',
            $this->getCurrentUrl(),
        );
        $expected .= sprintf(
            '    <img alt="" class="image-product" itemprop="image" data-src="%s/content-test/images/product/default/1.jpg" title="" src="%1$s/content-test/images/product/default/1.jpg" loading="lazy"/>',
            $this->getCurrentUrl(),
        );
        $expected .= '</picture>';

        $this->assertXmlStringEqualsXmlString($expected, $html);

        libxml_clear_errors();
    }

    public function testGetImageHtmlWithoutNativeLazyLoad(): void
    {
        $imageView = $this->createImageView();

        $readModelBundleImageExtension = $this->createImageExtension('', null, true);

        $this->overrideBrowserPropertyToSafari($readModelBundleImageExtension);

        $html = $readModelBundleImageExtension->getImageHtml($imageView);

        $expected = '<picture>';
        $expected .= sprintf(
            '    <source media="(min-width: 480px) and (max-width: 768px)" srcset="%s/content-test/images/product/default/additional_0_1.jpg"/>',
            $this->getCurrentUrl(),
        );
        $expected .= sprintf(
            '    <img alt="" class="image-product" itemprop="image" data-src="%s/content-test/images/product/default/1.jpg" title="" src="%1$s/placeholder.gif" loading="lazy"/>',
            $this->getCurrentUrl(),
        );
        $expected .= '</picture>';

        $this->assertXmlStringEqualsXmlString($expected, $html);

        libxml_clear_errors();
    }

    public function testGetImageHtmlWithtoutLazyload(): void
    {
        $imageView = $this->createImageView();

        $readModelBundleImageExtension = $this->createImageExtension();
        $html = $readModelBundleImageExtension->getImageHtml($imageView, ['lazy' => false]);

        $expected = '<picture>';
        $expected .= sprintf(
            '    <source media="(min-width: 480px) and (max-width: 768px)" srcset="%s/content-test/images/product/default/additional_0_1.jpg"/>',
            $this->getCurrentUrl(),
        );
        $expected .= sprintf(
            '    <img alt="" class="image-product" itemprop="image" src="%s/content-test/images/product/default/1.jpg" title=""/>',
            $this->getCurrentUrl(),
        );
        $expected .= '</picture>';

        $this->assertXmlStringEqualsXmlString($expected, $html);

        libxml_clear_errors();
    }

    public function testGetNoImageHtml(): void
    {
        $readModelBundleImageExtension = $this->createImageExtension('', null, true);

        $html = $readModelBundleImageExtension->getImageHtml(null);

        $expected = '<picture>';
        $expected .= sprintf(
            '    <img alt="" class="image-noimage" title=""  itemprop="image" src="%s/noimage.png"/>',
            $this->getCurrentUrl(),
        );
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
        $expected .= sprintf(
            '    <img alt="" class="image-noimage" title=""  itemprop="image" src="%s%snoimage.png"/>',
            $this->getCurrentUrl(),
            $defaultFrontDesignImageUrlPrefix,
        );
        $expected .= '</picture>';

        $this->assertXmlStringEqualsXmlString($expected, $html);

        libxml_clear_errors();
    }

    /**
     * @param \Shopsys\ReadModelBundle\Twig\ImageExtension $imageExtension
     */
    private function overrideBrowserPropertyToSafari(ImageExtension $imageExtension): void
    {
        $safariBrowser = new Browser();
        $safariBrowser->setName(Browser::SAFARI);

        $reflection = new ReflectionProperty(ImageExtension::class, 'browser');
        $reflection->setAccessible(true);
        $reflection->setValue($imageExtension, $safariBrowser);
    }

    /**
     * @return string
     */
    private function getCurrentUrl(): string
    {
        return $this->createOrGetDomainMock()->getCurrentDomainConfig()->getUrl();
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
        bool $enableLazyLoad = false,
    ): ImageExtension {
        $templating = self::getContainer()->get('twig');
        $imageFacade = $imageFacade ?: $this->imageFacade;

        return new ImageExtension(
            $frontDesignImageUrlPrefix,
            $this->createOrGetDomainMock(),
            $this->imageLocator,
            $imageFacade,
            $templating,
            $enableLazyLoad,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private function createOrGetDomainMock(): Domain
    {
        if (isset($this->domainMock)) {
            return $this->domainMock;
        }

        $settingMock = $this->getMockBuilder(Setting::class)->disableOriginalConstructor()->getMock();

        $domainConfig = new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://webserver:8080', 'webserver', 'en');
        $this->domainMock = new Domain([$domainConfig], $settingMock);
        $this->domainMock->switchDomainById(Domain::FIRST_DOMAIN_ID);

        return $this->domainMock;
    }

    /**
     * @return \Shopsys\ReadModelBundle\Image\ImageView
     */
    protected function createImageView(): ImageView
    {
        $productId = 1;
        $entityName = 'product';
        $fileExtension = 'jpg';

        return new ImageView($productId, $fileExtension, $entityName, null);
    }
}
