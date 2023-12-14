<?php

declare(strict_types=1);

namespace App\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedExport;
use Shopsys\FrameworkBundle\Model\Feed\FeedExportFactory as BaseFeedExportFactory;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;

class FeedExportFactory extends BaseFeedExportFactory
{
    /**
     * this error has been reported to github, after fixing it is possible to remove this function
     * https://github.com/shopsys/shopsys/issues/2039
     *
     * @param \Shopsys\FrameworkBundle\Model\Feed\FeedInterface $feed
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedExport
     */
    public function create(FeedInterface $feed, DomainConfig $domainConfig, ?int $lastSeekId = null): FeedExport
    {
        if ($lastSeekId !== null && !is_int($lastSeekId)) {
            @trigger_error(
                sprintf(
                    'The argument "$lastSeekId" passed to method "%s()" should be type of int or null.'
                    . ' Argument will be strict typed in the next major.',
                    __METHOD__,
                ),
                E_USER_DEPRECATED,
            );
        }

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
            $feedFilepath,
            $feedLocalFilepath,
            $this->servicesResetter,
            $lastSeekId,
        );
    }
}
