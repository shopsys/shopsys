<?php

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapDumperFactory
{
    protected const MAX_ITEMS_IN_FILE = 50000;

    protected EventDispatcherInterface $eventDispatcher;

    protected Filesystem $localFilesystem;

    protected SitemapFilePrefixer $sitemapFilePrefixer;

    protected MountManager $mountManager;

    protected FilesystemOperator $filesystem;

    protected UrlGeneratorInterface $urlGenerator;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapFilePrefixer $sitemapFilePrefixer
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Filesystem $localFilesystem,
        FilesystemOperator $filesystem,
        MountManager $mountManager,
        SitemapFilePrefixer $sitemapFilePrefixer,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->localFilesystem = $localFilesystem;
        $this->sitemapFilePrefixer = $sitemapFilePrefixer;
        $this->mountManager = $mountManager;
        $this->filesystem = $filesystem;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapDumper
     */
    public function createForDomain($domainId)
    {
        return new SitemapDumper(
            $this->eventDispatcher,
            $this->localFilesystem,
            $this->filesystem,
            $this->mountManager,
            $this->urlGenerator,
            $this->sitemapFilePrefixer->getSitemapFilePrefixForDomain($domainId),
            static::MAX_ITEMS_IN_FILE
        );
    }
}
