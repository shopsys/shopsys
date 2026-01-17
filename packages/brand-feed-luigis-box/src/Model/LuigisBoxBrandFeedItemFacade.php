<?php

declare(strict_types=1);

namespace Shopsys\BrandFeed\LuigisBoxBundle\Model;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandRepository;

class LuigisBoxBrandFeedItemFacade
{
    public function __construct(
        protected readonly LuigisBoxBrandFeedItemFactory $luigisBoxBrandFeedItemFactory,
        protected readonly BrandRepository $brandRepository,
    ) {
    }

    /**
     * @return iterable<int, \Shopsys\BrandFeed\LuigisBoxBundle\Model\LuigisBoxBrandFeedItem>
     */
    public function getItems(DomainConfig $domainConfig, ?int $lastSeekId, int $maxResults): iterable
    {
        $brands = $this->brandRepository->getAll();

        foreach ($brands as $brand) {
            yield $this->luigisBoxBrandFeedItemFactory->create($brand, $domainConfig);
        }
    }
}
