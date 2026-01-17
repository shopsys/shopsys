<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use Monolog\Logger;
use Override;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class SitemapCronModule implements SimpleCronModuleInterface
{
    public function __construct(protected readonly SitemapFacade $sitemapFacade)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setLogger(Logger $logger)
    {
    }

    #[Override]
    public function run()
    {
        $this->sitemapFacade->generateForAllDomains();
    }
}
