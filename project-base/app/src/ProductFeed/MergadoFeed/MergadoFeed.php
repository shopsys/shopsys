<?php

declare(strict_types=1);

namespace App\ProductFeed\MergadoFeed;

use App\Model\ProductFeed\Mergado\MergadoFeedInfo;
use App\Model\ProductFeed\Mergado\MergadoFeedItemFacade;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface;
use Shopsys\FrameworkBundle\Model\Feed\FeedInterface;

class MergadoFeed implements FeedInterface
{
    /**
     * @param \App\Model\ProductFeed\Mergado\MergadoFeedInfo $mergadoFeedInfo
     * @param \App\Model\ProductFeed\Mergado\MergadoFeedItemFacade $mergadoFeedItemFacade
     */
    public function __construct(
        private MergadoFeedInfo $mergadoFeedInfo,
        private MergadoFeedItemFacade $mergadoFeedItemFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Feed\FeedInfoInterface
     */
    public function getInfo(): FeedInfoInterface
    {
        return $this->mergadoFeedInfo;
    }

    /**
     * @return string
     */
    public function getTemplateFilepath(): string
    {
        return 'feed/mergadoFeed.xml.twig';
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return iterable
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        return $this->mergadoFeedItemFacade->getItems($domainConfig, $lastSeekId, $maxResults);
    }
}
