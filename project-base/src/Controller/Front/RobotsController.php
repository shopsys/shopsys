<?php

declare(strict_types=1);

namespace App\Controller\Front;

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
     * @var string
     */
    private $sitemapsUrlPrefix;

    /**
     * @param string $sitemapsUrlPrefix
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapFilePrefixer $sitemapFilePrefixer
     */
    public function __construct(
        string $sitemapsUrlPrefix,
        Domain $domain,
        SitemapFilePrefixer $sitemapFilePrefixer
    ) {
        $this->sitemapsUrlPrefix = $sitemapsUrlPrefix;
        $this->domain = $domain;
        $this->sitemapFilePrefixer = $sitemapFilePrefixer;
    }

    public function indexAction()
    {
        $sitemapFilePrefix = $this->sitemapFilePrefixer->getSitemapFilePrefixForDomain($this->domain->getId());

        $sitemapUrl = $this->domain->getUrl() . $this->sitemapsUrlPrefix . '/' . $sitemapFilePrefix . '.xml';

        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');

        return $this->render(
            '@ShopsysFramework/Common/robots.txt.twig',
            [
                'sitemapUrl' => $sitemapUrl,
            ],
            $response
        );
    }
}
