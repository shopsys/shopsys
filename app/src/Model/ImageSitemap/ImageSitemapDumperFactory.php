<?php

declare(strict_types=1);

namespace App\Model\ImageSitemap;

use League\Flysystem\FilesystemInterface;
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
     * @var \App\Model\ImageSitemap\ImageSitemapFilePrefixer
     */
    private ImageSitemapFilePrefixer $imageSitemapFilePrefixer;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param \League\Flysystem\FilesystemInterface $filesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapFilePrefixer $sitemapFilePrefixer
     * @param \App\Model\ImageSitemap\ImageSitemapFilePrefixer $imageSitemapFilePrefixer
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Filesystem $localFilesystem,
        FilesystemInterface $filesystem,
        MountManager $mountManager,
        SitemapFilePrefixer $sitemapFilePrefixer,
        ImageSitemapFilePrefixer $imageSitemapFilePrefixer,
        UrlGeneratorInterface $urlGenerator
    ) {
        parent::__construct($eventDispatcher, $localFilesystem, $filesystem, $mountManager, $sitemapFilePrefixer, $urlGenerator);

        $this->imageSitemapFilePrefixer = $imageSitemapFilePrefixer;
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
