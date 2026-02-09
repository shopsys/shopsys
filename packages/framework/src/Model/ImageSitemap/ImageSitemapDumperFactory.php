<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapDumper;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapDumperFactory;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapFilePrefixer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ImageSitemapDumperFactory extends SitemapDumperFactory
{
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Filesystem $localFilesystem,
        FilesystemOperator $filesystem,
        MountManager $mountManager,
        SitemapFilePrefixer $sitemapFilePrefixer,
        UrlGeneratorInterface $urlGenerator,
        TransformStringHelper $transformStringHelper,
        protected readonly ImageSitemapFilePrefixer $imageSitemapFilePrefixer,
    ) {
        parent::__construct($eventDispatcher, $localFilesystem, $filesystem, $mountManager, $sitemapFilePrefixer, $urlGenerator, $transformStringHelper);
    }

    public function createForImagesForDomain(int $domainId): SitemapDumper
    {
        return new ImageSitemapDumper(
            $this->eventDispatcher,
            $this->localFilesystem,
            $this->filesystem,
            $this->mountManager,
            $this->transformStringHelper,
            $this->urlGenerator,
            $this->imageSitemapFilePrefixer->getSitemapFilePrefixForDomain($domainId),
            static::MAX_ITEMS_IN_FILE,
        );
    }
}
