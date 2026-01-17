<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\MountManager;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Shopsys\FrameworkBundle\DependencyInjection\ServicesResetter;
use Symfony\Component\Filesystem\Filesystem;

class FeedExportFactory
{
    public function __construct(
        protected readonly FeedRendererFactory $feedRendererFactory,
        protected readonly FilesystemOperator $filesystem,
        protected readonly EntityManagerInterface $em,
        protected readonly FeedPathProvider $feedPathProvider,
        protected readonly Filesystem $localFilesystem,
        protected readonly MountManager $mountManager,
        protected readonly ServicesResetter $servicesResetter,
        protected readonly TransformStringHelper $transformStringHelper,
    ) {
    }

    public function create(FeedInterface $feed, DomainConfig $domainConfig, ?int $lastSeekId = null): FeedExport
    {
        $feedRenderer = $this->feedRendererFactory->create($feed);
        $feedFilepath = $this->feedPathProvider->getFeedFilepath($feed->getInfo(), $domainConfig);
        $feedLocalFilepath = $this->feedPathProvider->getFeedLocalFilepath($feed->getInfo(), $domainConfig);

        return new FeedExport(
            $feed,
            $domainConfig,
            $feedRenderer,
            $this->filesystem,
            $this->localFilesystem,
            $this->mountManager,
            $this->em,
            $this->transformStringHelper,
            $feedFilepath,
            $feedLocalFilepath,
            $this->servicesResetter,
            $lastSeekId,
        );
    }
}
