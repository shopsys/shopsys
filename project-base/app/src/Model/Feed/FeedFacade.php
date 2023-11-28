<?php

declare(strict_types=1);

namespace App\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedExport;
use Shopsys\FrameworkBundle\Model\Feed\FeedFacade as BaseFeedFacade;

/**
 * @property \App\Model\Feed\FeedExportFactory $feedExportFactory
 * @method __construct(\Shopsys\FrameworkBundle\Model\Feed\FeedRegistry $feedRegistry, \App\Model\Product\ProductVisibilityFacade $productVisibilityFacade, \App\Model\Feed\FeedExportFactory $feedExportFactory, \Shopsys\FrameworkBundle\Model\Feed\FeedPathProvider $feedPathProvider, \League\Flysystem\FilesystemOperator $filesystem, \Shopsys\FrameworkBundle\Model\Feed\FeedModuleRepository $feedModuleRepository, \Doctrine\ORM\EntityManagerInterface $em)
 */
class FeedFacade extends BaseFeedFacade
{
    /**
     * @param string $feedName
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedExport
     */
    public function createFeedExport(string $feedName, DomainConfig $domainConfig, ?int $lastSeekId = null): FeedExport
    {
        $feedConfig = $this->feedRegistry->getFeedConfigByName($feedName);

        return $this->feedExportFactory->create($feedConfig->getFeed(), $domainConfig, $lastSeekId);
    }
}
