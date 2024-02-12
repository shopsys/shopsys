<?php

declare(strict_types=1);

namespace Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem;

use Shopsys\CategoryFeed\LuigisBoxBundle\Model\Category\LuigisBoxCategoryRepository;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;

class LuigisBoxCategoryFeedItemFacade
{
    /**
     * @param \Shopsys\CategoryFeed\LuigisBoxBundle\Model\Category\LuigisBoxCategoryRepository $luigisBoxCategoryRepository
     * @param \Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxCategoryFeedItemFactory $luigisBoxCategoryFeedItemFactory
     */
    public function __construct(
        protected readonly LuigisBoxCategoryRepository $luigisBoxCategoryRepository,
        protected readonly LuigisBoxCategoryFeedItemFactory $luigisBoxCategoryFeedItemFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig
     * @param int|null $lastSeekId
     * @param int $maxResults
     * @return iterable<int, \Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxCategoryFeedItem>
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        $categories = $this->luigisBoxCategoryRepository->getCategories($domainConfig, $lastSeekId, $maxResults);

        foreach ($categories as $category) {
            yield $this->luigisBoxCategoryFeedItemFactory->create($category, $domainConfig);
        }
    }
}
