<?php

namespace Shopsys\FrameworkBundle\Model\Feed;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Symfony\Component\Filesystem\Filesystem;

class FeedExportFactory
{
    protected FeedRendererFactory $feedRendererFactory;

    protected FilesystemOperator $filesystem;

    protected EntityManagerInterface $em;

    protected FeedPathProvider $feedPathProvider;

    protected Filesystem $localFilesystem;

    protected MountManager $mountManager;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedRendererFactory $feedRendererFactory
     * @param \League\Flysystem\FilesystemOperator $filesystem
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedPathProvider $feedPathProvider
     * @param \Symfony\Component\Filesystem\Filesystem $localFilesystem
     * @param \League\Flysystem\MountManager $mountManager
     */
    public function __construct(
        FeedRendererFactory $feedRendererFactory,
        FilesystemOperator $filesystem,
        EntityManagerInterface $em,
        FeedPathProvider $feedPathProvider,
        Filesystem $localFilesystem,
        MountManager $mountManager
    ) {
        $this->feedRendererFactory = $feedRendererFactory;
        $this->filesystem = $filesystem;
        $this->em = $em;
        $this->feedPathProvider = $feedPathProvider;
        $this->localFilesystem = $localFilesystem;
        $this->mountManager = $mountManager;
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
            $lastSeekId
        );
    }
}
