<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ImageSitemap;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapDumper;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapDumperFactory;
use Shopsys\FrameworkBundle\Model\Sitemap\SitemapFilePrefixer;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ImageSitemapDumperFactory extends SitemapDumperFactory
{
    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapFilePrefixer $sitemapFilePrefixer
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param \Shopsys\FrameworkBundle\Model\ImageSitemap\ImageSitemapFilePrefixer $imageSitemapFilePrefixer
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Filesystem $localFilesystem,
        FilesystemOperator $filesystem,
        MountManager $mountManager,
        SitemapFilePrefixer $sitemapFilePrefixer,
        UrlGeneratorInterface $urlGenerator,
        protected readonly ImageSitemapFilePrefixer $imageSitemapFilePrefixer,
    ) {
        parent::__construct($eventDispatcher, $localFilesystem, $filesystem, $mountManager, $sitemapFilePrefixer, $urlGenerator);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapDumper
     */
    public function createForImagesForDomain(int $domainId): SitemapDumper
    {
        return new ImageSitemapDumper(
            $this->eventDispatcher,
            $this->localFilesystem,
            $this->filesystem,
            $this->mountManager,
            $this->urlGenerator,
            $this->imageSitemapFilePrefixer->getSitemapFilePrefixForDomain($domainId),
            static::MAX_ITEMS_IN_FILE
        );
    }
}
