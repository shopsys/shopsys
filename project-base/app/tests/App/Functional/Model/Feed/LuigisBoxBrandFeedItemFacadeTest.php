<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Feed;

use Shopsys\BrandFeed\LuigisBoxBundle\Model\LuigisBoxBrandFeedItemFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Tests\App\Test\TransactionFunctionalTestCase;

final class LuigisBoxBrandFeedItemFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private LuigisBoxBrandFeedItemFacade $luigisBoxBrandFeedItemFacade;

    public function testItemsAreReturnedInBatchesContinuingFromLastSeekId(): void
    {
        $domainConfig = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID);

        $firstBatchSeekIds = $this->getSeekIds($this->luigisBoxBrandFeedItemFacade->getItems($domainConfig, null, 2));
        $secondBatchSeekIds = $this->getSeekIds(
            $this->luigisBoxBrandFeedItemFacade->getItems($domainConfig, max($firstBatchSeekIds), 2),
        );

        $this->assertCount(2, $firstBatchSeekIds);
        $this->assertCount(2, $secondBatchSeekIds);
        $this->assertGreaterThan(max($firstBatchSeekIds), min($secondBatchSeekIds));
    }

    /**
     * @param iterable<int, \Shopsys\BrandFeed\LuigisBoxBundle\Model\LuigisBoxBrandFeedItem> $items
     * @return int[]
     */
    private function getSeekIds(iterable $items): array
    {
        $seekIds = [];

        foreach ($items as $item) {
            $seekIds[] = $item->getSeekId();
        }

        return $seekIds;
    }
}
