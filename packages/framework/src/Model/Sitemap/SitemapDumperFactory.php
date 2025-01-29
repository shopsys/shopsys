<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Sitemap;

use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SitemapDumperFactory
{
    protected const MAX_ITEMS_IN_FILE = 50000;

    /**
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Shopsys\FrameworkBundle\Model\Sitemap\SitemapFilePrefixer $sitemapFilePrefixer
     * @param \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator
     * @param \Shopsys\FrameworkBundle\Component\String\TransformStringHelper $transformStringHelper
     */
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly Filesystem $localFilesystem,
        protected readonly FilesystemOperator $filesystem,
        protected readonly MountManager $mountManager,
        protected readonly SitemapFilePrefixer $sitemapFilePrefixer,
        protected readonly UrlGeneratorInterface $urlGenerator,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Sitemap\SitemapDumper
     */
    public function createForDomain(int $domainId): SitemapDumper
    {
        return new SitemapDumper(
            $this->eventDispatcher,
            $this->localFilesystem,
            $this->filesystem,
            $this->mountManager,
            $this->transformStringHelper,
            $this->urlGenerator,
            $this->sitemapFilePrefixer->getSitemapFilePrefixForDomain($domainId),
            static::MAX_ITEMS_IN_FILE,
        );
    }
}
