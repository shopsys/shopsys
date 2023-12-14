<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;

class FeedExportFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedRendererFactory $feedRendererFactory
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedPathProvider $feedPathProvider
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param \League\Flysystem\MountManager $mountManager
     * @param \Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter $servicesResetter
     */
    public function __construct(
        protected readonly FeedRendererFactory $feedRendererFactory,
        protected readonly FilesystemOperator $filesystem,
        protected readonly EntityManagerInterface $em,
        protected readonly FeedPathProvider $feedPathProvider,
        protected readonly Filesystem $localFilesystem,
        protected readonly MountManager $mountManager,
        protected readonly ServicesResetter $servicesResetter,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInterface $feed
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedExport
     */
    public function create(FeedInterface $feed, DomainConfig $domainConfig, ?int $lastSeekId = null): FeedExport
    {
        $feedRenderer = $this->feedRendererFactory->create($feed);
        $feedFilepath = $this->feedPathProvider->getFeedFilepath($feed->getInfo(), $domainConfig);
        $feedLocalFilepath = $this->feedPathProvider->getFeedLocalFilepath($feed->getInfo(), $domainConfig);
        $lastSeekId = $lastSeekId !== null ? (int)$lastSeekId : $lastSeekId;

        return new FeedExport(
            $feed,
            $domainConfig,
            $feedRenderer,
            $this->filesystem,
            $this->localFilesystem,
            $this->mountManager,
            $this->em,
            $feedFilepath,
            $feedLocalFilepath,
            $this->servicesResetter,
            $lastSeekId,
        );
    }
}
