<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

use Psr\Log\LoggerInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class ImageSitemapCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapFacade $imageSitemapFacade
     */
    public function __construct(
        protected readonly ImageSitemapFacade $imageSitemapFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
    }

    public function run()
    {
        $this->imageSitemapFacade->generateForAllDomains();
    }
}
