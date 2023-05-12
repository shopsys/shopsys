<?php

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class SitemapCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapFacade $sitemapFacade
     */
    public function __construct(protected readonly SitemapFacade $sitemapFacade)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run()
    {
        $this->sitemapFacade->generateForAllDomains();
    }
}
