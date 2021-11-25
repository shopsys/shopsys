<?php

declare(strict_types=1);

namespace App\Model\ImageSitemap;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ImageSitemapCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Model\ImageSitemap\ImageSitemapFacade
     */
    private ImageSitemapFacade $imageSitemapFacade;

    /**
     * @param \App\Model\ImageSitemap\ImageSitemapFacade $imageSitemapFacade
     */
    public function __construct(ImageSitemapFacade $imageSitemapFacade)
    {
        $this->imageSitemapFacade = $imageSitemapFacade;
    }

    /**
     * @inheritdoc
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run()
    {
        $this->imageSitemapFacade->generateForAllDomains();
    }
}
