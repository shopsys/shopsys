<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\ImageSitemap;

use App\Model\ImageSitemap\ImageSitemapFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Tests\App\Test\ApplicationTestCase;

class ImageSitemapTest extends ApplicationTestCase
{
    /**
     * @var \App\Model\ImageSitemap\ImageSitemapFacade
     * @inject
     */
    private ImageSitemapFacade $imageSitemapFacade;

    public function testCreateImageSitemapXml(): void
    {
        $this->imageSitemapFacade->generateForAllDomains();

        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();
            $filename = 'web/content-test/sitemaps/domain_' . $domainId . '_sitemap_image.products.xml';
            $xml = simplexml_load_file($filename);

            $expected = $this->getExpectedXml($domainConfig);
            $unexpectedNotMainImage = $this->getUnexpectedNotMainXml($domainConfig);
            $unexpectedNotNotVisibleProduct = $this->getUnexpectedNotVisibleXml($domainConfig);

            $this->assertStringContainsStringIgnoringCase($expected, $xml->asXML());
            $this->assertStringNotContainsStringIgnoringCase($unexpectedNotMainImage, $xml->asXML());
            $this->assertStringNotContainsStringIgnoringCase($unexpectedNotNotVisibleProduct, $xml->asXML());
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    private function getExpectedXml(DomainConfig $domainConfig): string
    {
        $url = $domainConfig->getUrl();

        return '<url><loc>' . $url . '/televize-22-sencor-sle-22f46dm4-hello-kitty-plazmova</loc><image:image><image:loc>' . $url . '/content-test/images/product/default/22-sencor-sle-22f46dm4-hello-kitty_1.jpg</image:loc><image:title><![CDATA[22" Sencor SLE 22F46DM4 HELLO KITTY]]></image:title></image:image></url>';
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    private function getUnexpectedNotMainXml(DomainConfig $domainConfig): string
    {
        $url = $domainConfig->getUrl();

        return '<url><loc>' . $url . '/televize-22-sencor-sle-22f46dm4-hello-kitty-plazmova</loc><image:image><image:loc>' . $url . '/content/images/product/default/22-sencor-sle-22f46dm4-hello-kitty_64.jpg</image:loc><image:title><![CDATA[22" Sencor SLE 22F46DM4 HELLO KITTY]]></image:title></image:image></url>';
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @return string
     */
    private function getUnexpectedNotVisibleXml(DomainConfig $domainConfig): string
    {
        $url = $domainConfig->getUrl();

        return '<url><loc>' . $url . '/hadice-vp-9241</loc>';
    }
}
