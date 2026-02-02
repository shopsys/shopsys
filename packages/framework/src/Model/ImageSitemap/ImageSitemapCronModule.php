<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

use Monolog\Logger;
use Override;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class ImageSitemapCronModule implements SimpleCronModuleInterface
{
    public function __construct(
        protected readonly ImageSitemapFacade $imageSitemapFacade,
    ) {
    }

    #[Override]
    public function setLogger(Logger $logger): void
    {
    }

    #[Override]
    public function run(): void
    {
        $this->imageSitemapFacade->generateForAllDomains();
    }
}
