<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\ImageSitemap\ImageSitemapFilePrefixer;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapFilePrefixer;
use Symfony\Component\HttpFoundation\Response;

class RobotsController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Sitemap\SitemapFilePrefixer
     */
    private $sitemapFilePrefixer;

    /**
     * @var \App\Model\ImageSitemap\ImageSitemapFilePrefixer
     */
    private ImageSitemapFilePrefixer $imageSitemapFilePrefixer;

    /**
     * @var string
     */
    private $sitemapsUrlPrefix;

    /**
     * @param string $sitemapsUrlPrefix
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapFilePrefixer $sitemapFilePrefixer
     * @param \App\Model\ImageSitemap\ImageSitemapFilePrefixer $imageSitemapFilePrefixer
     */
    public function __construct(
        string $sitemapsUrlPrefix,
        Domain $domain,
        SitemapFilePrefixer $sitemapFilePrefixer,
        ImageSitemapFilePrefixer $imageSitemapFilePrefixer
    ) {
        $this->sitemapsUrlPrefix = $sitemapsUrlPrefix;
        $this->domain = $domain;
        $this->sitemapFilePrefixer = $sitemapFilePrefixer;
        $this->imageSitemapFilePrefixer = $imageSitemapFilePrefixer;
    }

    public function indexAction()
    {
        $domainId = $this->domain->getId();
        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');

        return $this->render(
            '@ShopsysFramework/Common/robots.txt.twig',
            [
                'sitemapUrl' => $this->getSitemapUrl($this->sitemapFilePrefixer->getSitemapFilePrefixForDomain($domainId)),
                'imageSitemapUrl' => $this->getSitemapUrl($this->imageSitemapFilePrefixer->getSitemapFilePrefixForDomain($domainId)),
            ],
            $response
        );
    }

    /**
     * @param string $filePrefix
     * @return string
     */
    private function getSitemapUrl(string $filePrefix): string
    {
        return $this->domain->getUrl() . $this->sitemapsUrlPrefix . '/' . $filePrefix . '.xml';
    }
}
