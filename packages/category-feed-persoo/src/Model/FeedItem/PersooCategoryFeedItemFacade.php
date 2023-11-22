<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\PersooBundle\Model\FeedItem;

use Shopsys\CategoryFeed\PersooBundle\Model\Category\PersooCategoryRepository;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class PersooCategoryFeedItemFacade
{
    /**
     * @param \Shopsys\CategoryFeed\PersooBundle\Model\Category\PersooCategoryRepository $persooCategoryRepository
     * @param \Shopsys\CategoryFeed\PersooBundle\Model\FeedItem\PersooCategoryFeedItemFactory $persooFeedItemFactory
     */
    public function __construct(
        protected readonly PersooCategoryRepository $persooCategoryRepository,
        protected readonly PersooCategoryFeedItemFactory $persooFeedItemFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return \Shopsys\CategoryFeed\PersooBundle\Model\FeedItem\PersooCategoryFeedItem[]|iterable
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        $categories = $this->persooCategoryRepository->getCategories($domainConfig, $lastSeekId, $maxResults);

        foreach ($categories as $category) {
            yield $this->persooFeedItemFactory->create($category, $domainConfig);
        }
    }
}
