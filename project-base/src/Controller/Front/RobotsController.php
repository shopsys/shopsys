<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\DependencyInjection\SetterInjectionTrait;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapFilePrefixer;
use Symfony\Component\HttpFoundation\Response;

class RobotsController extends FrontBaseController
{
    use SetterInjectionTrait;

    /**
     * @param string $sitemapsUrlPrefix
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapFilePrefixer $sitemapFilePrefixer
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade|null $seoSettingFacade
     */
    public function __construct(
        protected readonly string $sitemapsUrlPrefix,
        protected readonly Domain $domain,
        protected readonly SitemapFilePrefixer $sitemapFilePrefixer,
        protected ?SeoSettingFacade $seoSettingFacade = null,
    ) {
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setSeoSettingFacade(SeoSettingFacade $seoSettingFacade): void
    {
        $this->setDependency($seoSettingFacade, 'seoSettingFacade');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        $sitemapFilePrefix = $this->sitemapFilePrefixer->getSitemapFilePrefixForDomain($this->domain->getId());

        $sitemapUrl = $this->domain->getUrl() . $this->sitemapsUrlPrefix . '/' . $sitemapFilePrefix . '.xml';
        $imageSitemapUrl = $this->domain->getUrl() . $this->sitemapsUrlPrefix . '/' . $sitemapFilePrefix . '_image' . '.xml';
        $customContent = $this->seoSettingFacade->getRobotsTxtContent($this->domain->getId());

        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');

        return $this->render(
            '@ShopsysFramework/Common/robots.txt.twig',
            [
                'sitemapUrl' => $sitemapUrl,
                'imageSitemapUrl' => $imageSitemapUrl,
                'customContent' => $customContent,
            ],
            $response,
        );
    }
}
