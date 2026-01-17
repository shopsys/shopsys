<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\ImageSitemap;

use Shopsys\FrameworkBundle\Component\DataFixture\DomainsForDataFixtureProvider;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapFacade;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Tests\App\Test\ApplicationTestCase;

class ImageSitemapTest extends ApplicationTestCase
{
    /**
     * @inject
     */
    private ImageSitemapFacade $imageSitemapFacade;

    /**
     * @inject
     */
    private ParameterBagInterface $parameterBag;

    /**
     * @inject
     */
    private DomainsForDataFixtureProvider $domainsForDataFixtureProvider;

    /**
     * @inject
     */
    private TransformStringHelper $transformStringHelper;

    public function testCreateImageSitemapXml(): void
    {
        $this->imageSitemapFacade->generateForAllDomains();
        $sitemapDir = $this->parameterBag->get('kernel.project_dir') . $this->parameterBag->get('shopsys.sitemaps_dir');

        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            if ($this->domain->isMainDomainWithinSameBaseUrlGroup($domainConfig) === false) {
                continue;
            }

            $domainId = $domainConfig->getId();

            foreach ($this->domain->getAllWithSameBaseUrl($domainConfig) as $relevantDomainConfig) {
                $filename = sprintf('%s/domain_%d_sitemap_image.%s.xml', $sitemapDir, $domainId, $this->imageSitemapFacade->getProductsSectionName($relevantDomainConfig));
                $xml = simplexml_load_file($filename);

                $expectedRegular = $this->getExpectedXmlRegex($relevantDomainConfig);
                $unexpectedNotMainImage = $this->getUnexpectedNotMainXml($relevantDomainConfig);
                $unexpectedNotNotVisibleProduct = $this->getUnexpectedNotVisibleXml($relevantDomainConfig);

                $this->assertMatchesRegularExpression($expectedRegular, $xml->asXML());
                $this->assertStringNotContainsStringIgnoringCase($unexpectedNotMainImage, $xml->asXML());
                $this->assertStringNotContainsStringIgnoringCase($unexpectedNotNotVisibleProduct, $xml->asXML());
            }
        }
    }

    private function getExpectedXmlRegex(DomainConfig $domainConfig): string
    {
        $urlPattern = preg_quote($domainConfig->getUrl(), '~');
        $basUrlPattern = preg_quote($domainConfig->getBaseUrl(), '~');
        $television = $this->transformStringHelper->stringToFriendlyUrlSlug(t('Television', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale()));
        $plasma = $this->transformStringHelper->stringToFriendlyUrlSlug(t('plasma', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale()));

        return '~<url><loc>' . $urlPattern . '/' . $television . '-22-sencor-sle-22f46dm4-hello-kitty-' . $plasma . '</loc><image\:image><image\:loc>' . $basUrlPattern . '/content-test/images/product/22-sencor-sle-22f46dm4-hello-kitty_1\.jpg</image\:loc></image\:image></url>~';
    }

    private function getUnexpectedNotMainXml(DomainConfig $domainConfig): string
    {
        $url = $domainConfig->getUrl();

        return '<url><loc>' . $url . '/televize-22-sencor-sle-22f46dm4-hello-kitty-plazmova</loc><image:image><image:loc>' . $url . '/content/images/product/22-sencor-sle-22f46dm4-hello-kitty_64.jpg</image:loc></image:image></url>';
    }

    private function getUnexpectedNotVisibleXml(DomainConfig $domainConfig): string
    {
        $url = $domainConfig->getUrl();

        return '<url><loc>' . $url . '/hadice-vp-9241</loc>';
    }
}
